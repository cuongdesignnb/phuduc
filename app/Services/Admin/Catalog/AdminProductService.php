<?php

namespace App\Services\Admin\Catalog;

use App\Models\MediaLibrary;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Admin\AdminConcurrencyService;
use App\Services\Admin\AdminPageService;
use App\Services\Admin\AdminPresentationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminProductService
{
    public function __construct(private readonly AdminProductPresentationService $presentation, private readonly ProductImageService $images, private readonly ProductReferenceService $references, private readonly AdminPageService $pages, private readonly AdminPresentationService $adminPresentation, private readonly AdminConcurrencyService $concurrency) {}

    public function index(User $user, array $filters): array
    {
        $sort = $filters['sort'] ?? 'latest';
        $direction = $filters['direction'] ?? 'desc';
        $paginator = Product::query()->with(['cardImage' => fn ($query) => $query->select([
            'product_images.id',
            'product_images.product_id',
            'product_images.image_path',
            'product_images.is_360',
            'product_images.sort_order',
        ])])->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($query) use ($search): void {
            $escaped = '%'.addcslashes($search, '%_\\').'%';
            $query->where('name', 'like', $escaped)->orWhere('sku', 'like', $escaped);
        }))->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))->orderBy($sort === 'latest' ? 'updated_at' : $sort, $direction)->paginate(15)->withQueryString();
        $referenceMap = $this->references->forProducts($paginator->getCollection()->pluck('id')->all());

        return $this->pages->envelope($user, 'admin_products_index', 'Sản phẩm', [['label' => 'Sản phẩm', 'url' => route('admin.products.index')]], ['items' => $paginator->getCollection()->map(fn (Product $product) => $this->presentation->item($product, $referenceMap[$product->id] ?? []))->values()->all(), 'pagination' => $this->adminPresentation->pagination($paginator), 'filters' => ['search' => $filters['search'] ?? '', 'status' => $filters['status'] ?? '', 'sort' => $sort, 'direction' => $direction]]);
    }

    public function editPage(User $user, ?Product $product): array
    {
        $module = ['product' => $product ? $this->presentation->edit($product->load(['images', 'variants']), $this->references->references($product)) : null, 'statuses' => [['key' => 'active', 'label' => 'Đang bán'], ['key' => 'inactive', 'label' => 'Ngừng bán']]];
        $label = $product ? 'Sửa sản phẩm' : 'Thêm sản phẩm';

        return $this->pages->envelope($user, 'admin_products_edit', $label, [['label' => 'Sản phẩm', 'url' => route('admin.products.index')], ['label' => $label, 'url' => $product ? route('admin.products.edit', $product) : null]], $module);
    }

    public function store(array $data): Product
    {
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name']);
        $data['price'] = (int) ($data['price'] ?? 0);
        $data['stock'] = (int) ($data['stock'] ?? 0);
        $data['specifications'] = $this->specifications($data['specifications'] ?? []);
        $variants = $this->variants($data['variants'] ?? []);
        unset($data['variants']);

        $product = Product::create($data);
        $this->syncVariants($product, $variants);

        return $product->refresh();
    }

    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data): Product {
            $locked = Product::query()->lockForUpdate()->findOrFail($product->id);
            $this->concurrency->assertVersion($data['version'] ?? null, $locked, 'Sản phẩm đã được cập nhật ở phiên khác. Vui lòng tải lại.');
            $payload = $data;
            $payload['slug'] = $payload['slug'] ?: $this->uniqueSlug($payload['name'], $locked->id);
            $payload['price'] = (int) ($payload['price'] ?? 0);
            $payload['stock'] = (int) ($payload['stock'] ?? 0);
            $payload['specifications'] = $this->specifications($payload['specifications'] ?? []);
            $variants = $this->variants($payload['variants'] ?? []);
            unset($payload['version']);
            unset($payload['variants']);
            $locked->update($payload);
            $this->syncVariants($locked, $variants);

            return $locked->refresh();
        });
    }

    public function destroy(Product $product): void
    {
        $references = $this->references->references($product);
        if ($references !== []) {
            throw ValidationException::withMessages(['product' => 'Sản phẩm đang được sử dụng và chưa thể xóa.', 'references' => $references]);
        }
        $paths = $product->images()->pluck('image_path')->filter(fn ($path) => str_starts_with((string) $path, 'products/'.$product->id.'/'))->values()->all();
        DB::transaction(function () use ($product): void {
            ProductImage::query()->where('product_id', $product->id)->delete();
            $product->delete();
        });
        DB::afterCommit(function () use ($paths): void {
            foreach ($paths as $path) {
                Storage::disk('public')->delete($path);
            }
        });
    }

    public function upload(Product $product, array $files, bool $is360): void
    {
        $this->images->upload($product, $files, $is360);
    }

    public function attach(Product $product, int $mediaId, bool $is360): void
    {
        $this->images->attach($product, MediaLibrary::query()->findOrFail($mediaId), $is360);
    }

    public function attachMany(Product $product, array $mediaIds, bool $is360): void
    {
        $this->images->attachMany($product, $mediaIds, $is360);
    }

    public function deleteImage(Product $product, $image): void
    {
        $this->images->delete($product, $image);
    }

    public function reorder(Product $product, array $order): void
    {
        $this->images->reorder($product, $order);
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'san-pham';
        $slug = $base;
        $suffix = 2;
        while (Product::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.($suffix++);
        }

        return $slug;
    }

    private function specifications(array $specifications): array
    {
        return collect($specifications)->map(fn ($item) => ['key' => trim((string) ($item['key'] ?? '')), 'value' => trim((string) ($item['value'] ?? ''))])->filter(fn ($item) => $item['key'] !== '')->values()->all();
    }

    private function variants(array $variants): array
    {
        return collect($variants)
            ->map(fn ($variant) => [
                'name' => trim((string) ($variant['name'] ?? '')),
                'image_id' => filter_var($variant['image_id'] ?? null, FILTER_VALIDATE_INT) ?: null,
                'sku' => trim((string) ($variant['sku'] ?? '')) ?: null,
                'price' => (int) ($variant['price'] ?? 0),
                'stock' => (int) ($variant['stock'] ?? 0),
                'note' => trim((string) ($variant['note'] ?? '')) ?: null,
                'status' => in_array($variant['status'] ?? 'active', ['active', 'inactive'], true) ? $variant['status'] : 'active',
            ])
            ->filter(fn (array $variant) => $variant['name'] !== '')
            ->values()
            ->all();
    }

    private function syncVariants(Product $product, array $variants): void
    {
        ProductVariant::query()->where('product_id', $product->id)->delete();

        foreach ($variants as $index => $variant) {
            $imageId = $variant['image_id'] ?? null;
            if ($imageId && ! ProductImage::query()->where('product_id', $product->id)->whereKey($imageId)->exists()) {
                $imageId = null;
            }
            unset($variant['image_id']);
            $product->variants()->create([...$variant, 'product_image_id' => $imageId, 'sort_order' => $index]);
        }
    }
}
