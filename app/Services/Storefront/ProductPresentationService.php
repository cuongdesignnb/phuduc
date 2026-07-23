<?php

namespace App\Services\Storefront;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use Illuminate\Support\Str;

class ProductPresentationService
{
    private const SPECIFICATION_ALIASES = [
        'payload' => ['payload', 'tai trong', 'tai trong nang', 'so cho ngoi'],
        'range' => ['range', 'quang duong', 'pham vi', 'pham vi hoat dong'],
        'battery' => ['battery', 'pin', 'ac quy', 'dung luong pin'],
    ];

    private const SPECIFICATION_LABELS = [
        'payload' => 'Tai trong',
        'range' => 'Quang duong',
        'battery' => 'Pin',
    ];

    public function __construct(private readonly MediaUrlService $mediaUrl) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Product $product): array
    {
        $specifications = $this->normalizeSpecifications($product->specifications);
        $price = $product->price !== null ? (float) $product->price : null;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'price' => $price,
            'price_display' => $this->priceDisplay($price),
            'image_url' => $this->mediaUrl->resolve($product->cardImage?->image_path),
            'specifications' => $specifications,
            'card_specifications' => $this->cardSpecifications($specifications),
            'review_count' => (int) ($product->approved_reviews_count ?? 0),
            'average_rating' => $product->approved_reviews_avg_rating !== null
                ? round((float) $product->approved_reviews_avg_rating, 1)
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(Product $product): array
    {
        $specifications = $this->normalizeSpecifications($product->specifications);
        $price = $product->price !== null ? (float) $product->price : null;
        $reviewCount = (int) ($product->approved_reviews_count ?? 0);
        $averageRating = $product->approved_reviews_avg_rating !== null
            ? round((float) $product->approved_reviews_avg_rating, 1)
            : null;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'description_html' => $product->description,
            'price' => $price,
            'price_display' => $this->priceDisplay($price),
            'stock' => (int) $product->stock,
            'gallery' => $product->images
                ->where('is_360', false)
                ->sortBy([['sort_order', 'asc'], ['id', 'asc']])
                ->map(fn (ProductImage $image) => $this->presentImage($image, $product->name))
                ->values()
                ->all(),
            'spin_frames' => $product->images
                ->where('is_360', true)
                ->sortBy([['sort_order', 'asc'], ['id', 'asc']])
                ->map(fn (ProductImage $image) => $this->presentImage($image, $product->name))
                ->values()
                ->all(),
            'specifications' => $specifications,
            'review_summary' => [
                'count' => $reviewCount,
                'average_rating' => $averageRating,
                'best_rating' => 5,
            ],
            'reviews' => $product->approvedReviews
                ->map(fn (Review $review) => $this->presentReview($review))
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<int, mixed>|null  $specifications
     * @return list<array{key: string, label: string, value: string}>
     */
    public function normalizeSpecifications(?array $specifications): array
    {
        return collect($specifications ?? [])
            ->filter(fn ($item) => is_array($item)
                && filled($item['key'] ?? null)
                && filled($item['value'] ?? null))
            ->map(fn ($item) => [
                'key' => trim((string) $item['key']),
                'label' => trim((string) ($item['label'] ?? $item['key'])),
                'value' => trim((string) $item['value']),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array{key: string, label: string, value: string}>  $specifications
     * @return list<array{key: string, label: string, value: string}>
     */
    public function cardSpecifications(array $specifications): array
    {
        $normalized = collect($specifications)->keyBy(
            fn (array $item) => Str::lower($this->fold($item['key'])),
        );

        $cards = collect(self::SPECIFICATION_ALIASES)
            ->map(function (array $aliases, string $key) use ($normalized) {
                $match = collect($aliases)
                    ->map(fn (string $alias) => $normalized->get(Str::lower($this->fold($alias))))
                    ->first();

                return $match ? [
                    'key' => $key,
                    'label' => self::SPECIFICATION_LABELS[$key],
                    'value' => $match['value'],
                ] : null;
            })
            ->filter()
            ->take(3)
            ->values()
            ->all();

        if ($cards !== []) {
            return $cards;
        }

        return collect($specifications)
            ->take(3)
            ->map(fn (array $item) => [
                'key' => $item['key'],
                'label' => $item['label'],
                'value' => $item['value'],
            ])
            ->values()
            ->all();
    }

    private function priceDisplay(?float $price): string
    {
        return $price !== null && $price > 0
            ? number_format($price, 0, ',', '.').' VND'
            : 'Lien he';
    }

    private function fold(string $value): string
    {
        return str_replace('d', 'd', Str::ascii(Str::lower(trim($value))));
    }

    /**
     * @return array{id: int, url: ?string, alt: string, sort_order: int}
     */
    private function presentImage(ProductImage $image, string $fallbackAlt): array
    {
        return [
            'id' => $image->id,
            'url' => $this->mediaUrl->resolve($image->image_path),
            'alt' => $fallbackAlt,
            'sort_order' => (int) $image->sort_order,
        ];
    }

    /**
     * @return array{id: int, customer_name: string, initial: string, rating: int, content: string, created_at: ?string, created_at_display: string}
     */
    private function presentReview(Review $review): array
    {
        $name = trim($review->customer_name);

        return [
            'id' => $review->id,
            'customer_name' => $name,
            'initial' => Str::upper(Str::substr($name, 0, 1)),
            'rating' => (int) $review->rating,
            'content' => $review->content,
            'created_at' => $review->created_at?->toIso8601String(),
            'created_at_display' => $review->created_at?->format('d/m/Y') ?? '',
        ];
    }
}
