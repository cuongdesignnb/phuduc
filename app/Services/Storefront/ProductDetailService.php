<?php

namespace App\Services\Storefront;

use App\Models\Product;

class ProductDetailService
{
    public function __construct(
        private readonly ProductPresentationService $products,
        private readonly StorefrontSeoService $seo,
        private readonly RichHtmlSanitizer $sanitizer,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function page(string $slug): array
    {
        $product = Product::query()
            ->select(['id', 'name', 'slug', 'description', 'price', 'sku', 'stock', 'specifications', 'status', 'created_at', 'updated_at'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->with([
                'images:id,product_id,image_path,is_360,sort_order',
                'approvedReviews' => fn ($query) => $query
                    ->select(['id', 'product_id', 'customer_name', 'content', 'rating', 'created_at'])
                    ->latest()
                    ->limit(20),
            ])
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->firstOrFail();

        $product->description = $this->sanitizer->sanitize($product->description);
        $presented = $this->products->detail($product);
        $related = Product::query()
            ->select(['id', 'name', 'slug', 'price', 'sku', 'specifications', 'status', 'created_at'])
            ->where('status', 'active')
            ->whereKeyNot($product->id)
            ->with('cardImage')
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(4)
            ->get()
            ->map(fn (Product $product) => $this->products->present($product))
            ->values()
            ->all();
        $breadcrumbs = [
            ['name' => 'Trang chủ', 'url' => url('/')],
            ['name' => 'Sản phẩm', 'url' => route('products.index')],
            ['name' => $presented['name'], 'url' => route('products.show', $presented['slug'])],
        ];

        return [
            'page' => [
                'type' => 'product_detail',
                'seo' => $this->seo->meta([
                    'title' => $presented['name'],
                    'description' => mb_substr(strip_tags((string) $presented['description_html']), 0, 160),
                    'ogImage' => $presented['gallery'][0]['url'] ?? null,
                    'ogType' => 'product',
                    'canonical' => route('products.show', $presented['slug']),
                ]),
                'json_ld' => [
                    $this->seo->productJsonLd($presented),
                    $this->seo->breadcrumbJsonLd($breadcrumbs),
                ],
                'breadcrumbs' => $breadcrumbs,
                'hero' => [
                    'eyebrow' => $presented['sku'] ? 'SKU: '.$presented['sku'] : 'Sản phẩm',
                    'title' => $presented['name'],
                    'description' => mb_substr(strip_tags((string) $presented['description_html']), 0, 140),
                ],
                'product' => $presented,
                'related_products' => $related,
            ],
        ];
    }
}
