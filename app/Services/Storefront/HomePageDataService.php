<?php

namespace App\Services\Storefront;

use App\Models\HomeSection;
use App\Models\HomeSectionItem;
use App\Models\Post;
use App\Models\Product;
use App\Support\Homepage\HomeSectionRegistry;
use Illuminate\Support\Facades\Schema;

class HomePageDataService
{
    public function __construct(
        private readonly ProductPresentationService $products,
        private readonly MediaUrlService $mediaUrl,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function sections(): array
    {
        if (! Schema::hasTable('home_sections')) {
            return [];
        }

        return HomeSection::query()
            ->where('is_enabled', true)
            ->whereIn('key', HomeSectionRegistry::keys())
            ->with(['activeItems' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (HomeSection $section) => $this->presentSection($section))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function presentSection(HomeSection $section): array
    {
        $definition = HomeSectionRegistry::get($section->key);
        $config = array_replace_recursive($definition['defaults'], $section->settings_json ?? []);

        return [
            'key' => $section->key,
            'type' => $section->type ?: $definition['type'],
            'enabled' => true,
            'sort_order' => $section->sort_order,
            'variant' => $section->variant ?: $definition['allowed_variants'][0],
            'heading' => [
                'eyebrow' => $config['eyebrow'] ?? null,
                'title' => $section->title,
                'subtitle' => $section->subtitle,
                'description' => $section->description,
            ],
            'config' => $this->publicConfig($config),
            'items' => $this->items($section, $config),
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private function publicConfig(array $config): array
    {
        foreach (['image'] as $key) {
            if (array_key_exists($key, $config)) {
                $config[$key.'_url'] = $this->mediaUrl->resolve($config[$key]);
                unset($config[$key]);
            }
        }

        return $config;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return list<array<string, mixed>>
     */
    private function items(HomeSection $section, array $config): array
    {
        return match ($section->type) {
            'product_collection' => $this->productItems($config),
            'post_collection' => $this->postItems($config),
            default => $section->activeItems
                ->map(fn (HomeSectionItem $item) => $this->presentItem($item))
                ->values()
                ->all(),
        };
    }

    /**
     * @param  array<string, mixed>  $config
     * @return list<array<string, mixed>>
     */
    private function productItems(array $config): array
    {
        $limit = max(1, min(12, (int) ($config['limit'] ?? 4)));
        $source = $config['source'] ?? 'manual';
        $ids = collect($config['product_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();

        $query = Product::query()
            ->where('status', 'active')
            ->select(['id', 'name', 'slug', 'sku', 'price', 'specifications', 'created_at'])
            ->with(['cardImage' => fn ($query) => $query->select([
                'product_images.id',
                'product_images.product_id',
                'product_images.image_path',
                'product_images.sort_order',
                'product_images.is_360',
            ])])
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating');

        if ($source === 'latest') {
            return $query->latest('created_at')->latest('id')->limit($limit)->get()
                ->map(fn (Product $product) => $this->products->present($product))->values()->all();
        }

        if ($ids->isEmpty()) {
            return [];
        }

        $products = $query->whereIn('id', $ids)->get()->keyBy('id');

        return $ids->take($limit)
            ->map(fn (int $id) => $products->get($id))
            ->filter()
            ->map(fn (Product $product) => $this->products->present($product))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $config
     * @return list<array<string, mixed>>
     */
    private function postItems(array $config): array
    {
        $limit = max(1, min(12, (int) ($config['limit'] ?? 3)));
        $ids = collect($config['post_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        $query = Post::query()
            ->where('status', 'published')
            ->select(['id', 'post_category_id', 'title', 'slug', 'summary', 'featured_image', 'created_at'])
            ->with('category:id,name');

        if (($config['source'] ?? 'latest') === 'manual') {
            $posts = $query->whereIn('id', $ids)->get()->keyBy('id');
            $collection = $ids->take($limit)->map(fn (int $id) => $posts->get($id))->filter();
        } else {
            $collection = $query->latest('created_at')->latest('id')->limit($limit)->get();
        }

        return $collection->map(fn (Post $post) => [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'summary' => $post->summary,
            'image_url' => $this->mediaUrl->resolve($post->featured_image),
            'category' => $post->category?->name,
            'published_at' => $post->created_at?->toDateString(),
        ])->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function presentItem(HomeSectionItem $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'subtitle' => $item->subtitle,
            'description' => $item->description,
            'image_url' => $this->mediaUrl->resolve($item->image),
            'icon' => $item->icon,
            'url' => $item->url,
            'metadata' => $item->metadata_json ?? [],
            'enabled' => true,
            'sort_order' => $item->sort_order,
        ];
    }
}
