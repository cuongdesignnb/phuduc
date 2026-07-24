<?php

namespace App\Services\Admin\Catalog;

use App\Models\MediaLibrary;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductImageService
{
    public function upload(Product $product, array $files, bool $is360): void
    {
        $nextOrder = ((int) $product->images()->max('sort_order')) + 1;
        $paths = [];

        try {
            DB::transaction(function () use ($product, $files, $is360, &$nextOrder, &$paths): void {
                foreach ($files as $file) {
                    if (! $file instanceof UploadedFile) continue;
                    $path = 'products/'.$product->id.'/'.Str::uuid().'.'.strtolower($file->extension());
                    $file->storeAs('products/'.$product->id, basename($path), 'public');
                    $paths[] = $path;
                    $product->images()->create(['image_path' => $path, 'is_360' => $is360, 'sort_order' => $nextOrder++]);
                }
            });
        } catch (\Throwable $exception) {
            foreach ($paths as $path) Storage::disk('public')->delete($path);
            throw $exception;
        }
    }

    public function attach(Product $product, MediaLibrary $media, bool $is360): ProductImage
    {
        $path = 'products/'.$product->id.'/'.Str::uuid().'.'.strtolower(pathinfo($media->file_path, PATHINFO_EXTENSION) ?: 'bin');
        if (! Storage::disk('public')->copy($media->file_path, $path)) {
            throw ValidationException::withMessages(['media_id' => 'Không thể sao chép Media vào sản phẩm.']);
        }

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
        if ($image->product_id !== $product->id) abort(404);
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
            throw ValidationException::withMessages(['order' => 'Danh sách ảnh phải thuộc cùng một sản phẩm.']);
        }

        DB::transaction(function () use ($ids): void {
            foreach ($ids as $sortOrder => $id) ProductImage::query()->whereKey($id)->update(['sort_order' => $sortOrder]);
        });
    }
}
