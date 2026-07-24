<?php

namespace App\Services\Admin\Catalog;

use App\Models\MediaLibrary;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Admin\Media\AdminImageStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductImageService
{
    public function __construct(private readonly AdminImageStorageService $storage) {}

    public function upload(Product $product, array $files, bool $is360): void
    {
        $nextOrder = ((int) $product->images()->max('sort_order')) + 1;
        $paths = [];

        try {
            DB::transaction(function () use ($product, $files, $is360, &$nextOrder, &$paths): void {
                foreach ($files as $file) {
                    if (! $file instanceof UploadedFile) {
                        continue;
                    }
                    $stored = $this->storage->store($file, 'products/'.$product->id);
                    $paths[] = $stored['path'];
                    $product->images()->create(['image_path' => $stored['path'], 'is_360' => $is360, 'sort_order' => $nextOrder++]);
                }
            });
        } catch (\Throwable $exception) {
            foreach ($paths as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $exception;
        }
    }

    public function attach(Product $product, MediaLibrary $media, bool $is360): ProductImage
    {
        $path = $this->storage->copyMedia($media, 'products/'.$product->id);

        try {
            return DB::transaction(fn () => $product->images()->create([
                'image_path' => $path,
                'is_360' => $is360,
                'sort_order' => ((int) $product->images()->max('sort_order')) + 1,
            ]));
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }
    }

    public function delete(Product $product, ProductImage $image): void
    {
        if ($image->product_id !== $product->id) {
            abort(404);
        }
        $path = $image->image_path;
        DB::transaction(fn () => $image->delete());
        if (str_starts_with($path, 'products/'.$product->id.'/')) {
            DB::afterCommit(fn () => Storage::disk('public')->delete($path));
        }
    }

    public function reorder(Product $product, array $order): void
    {
        $ids = array_map('intval', $order);
        $owned = $product->images()->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $submitted = $ids;
        sort($submitted);
        if ($owned !== $submitted) {
            throw ValidationException::withMessages(['order' => 'Image order must contain only images from this product.']);
        }

        DB::transaction(function () use ($ids): void {
            foreach ($ids as $sortOrder => $id) {
                ProductImage::query()->whereKey($id)->update(['sort_order' => $sortOrder]);
            }
        });
    }
}
