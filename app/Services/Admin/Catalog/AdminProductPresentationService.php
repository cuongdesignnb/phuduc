<?php

namespace App\Services\Admin\Catalog;

use App\Models\Product;
use App\Services\Admin\AdminPresentationService;
use App\Services\Storefront\MediaUrlService;

class AdminProductPresentationService
{
    public function __construct(private readonly AdminPresentationService $presentation, private readonly MediaUrlService $mediaUrl) {}

    public function item(Product $product, array $references = []): array
    {
        return ['id' => $product->id, 'name' => $product->name, 'slug' => $product->slug, 'sku' => $product->sku, 'price' => (int) round((float) $product->price), 'price_display' => $this->presentation->money($product->price)['display'], 'stock' => (int) $product->stock, 'stock_label' => (int) $product->stock > 0 ? 'Còn hàng' : 'Hết hàng', 'status' => $this->presentation->status($product->status), 'image_url' => $this->mediaUrl->resolve($product->cardImage?->image_path), 'updated_at_display' => $this->presentation->date($product->updated_at), 'edit_url' => route('admin.products.edit', $product), 'delete_url' => route('admin.products.destroy', $product), 'can_delete' => $references === [], 'delete_references' => $references];
    }

    public function edit(Product $product, array $references = []): array
    {
        return ['id' => $product->id, 'name' => $product->name, 'slug' => $product->slug, 'sku' => $product->sku, 'description' => $product->description, 'price' => (int) round((float) $product->price), 'stock' => (int) $product->stock, 'status' => $product->status, 'specifications' => collect($product->specifications ?? [])->map(fn ($spec) => ['key' => $spec['key'] ?? '', 'value' => $spec['value'] ?? ''])->values()->all(), 'images' => $product->images->map(fn ($image) => ['id' => $image->id, 'url' => $this->mediaUrl->resolve($image->image_path), 'is_360' => (bool) $image->is_360, 'sort_order' => (int) $image->sort_order, 'file_name' => basename($image->image_path)])->values()->all(), 'version' => (string) optional($product->updated_at)->toISOString(), 'delete_references' => $references];
    }
}
