<?php

namespace App\Services\Storefront;

use App\Models\Product;
use Illuminate\Support\Str;

class ProductPresentationService
{
    private const SPECIFICATION_ALIASES = [
        'load' => ['Tải trọng', 'Tải trọng nâng', 'Số chỗ ngồi'],
        'range' => ['Quãng đường', 'Phạm vi hoạt động', 'Phạm vi'],
        'battery' => ['Pin', 'Ắc quy', 'Dung lượng pin'],
    ];

    private const SPECIFICATION_LABELS = [
        'load' => 'Tải trọng',
        'range' => 'Quãng đường',
        'battery' => 'Pin',
    ];

    public function __construct(private readonly MediaUrlService $mediaUrl) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Product $product): array
    {
        $specifications = $this->normalizeSpecifications($product->specifications);
        $price = $product->price !== null ? (string) $product->price : null;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'price' => $price,
            'price_display' => $price !== null && (float) $price > 0
                ? number_format((float) $price, 0, ',', '.').'₫'
                : 'Liên hệ',
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
     * @param  array<int, mixed>|null  $specifications
     * @return list<array{key: string, value: string}>
     */
    public function normalizeSpecifications(?array $specifications): array
    {
        return collect($specifications ?? [])
            ->filter(fn ($item) => is_array($item)
                && filled($item['key'] ?? null)
                && filled($item['value'] ?? null))
            ->map(fn ($item) => [
                'key' => trim((string) $item['key']),
                'value' => trim((string) $item['value']),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array{key: string, value: string}>  $specifications
     * @return list<array{key: string, label: string, value: string}>
     */
    public function cardSpecifications(array $specifications): array
    {
        $normalized = collect($specifications)->keyBy(
            fn (array $item) => Str::lower($this->fold($item['key'])),
        );

        return collect(self::SPECIFICATION_ALIASES)
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
    }

    private function fold(string $value): string
    {
        return str_replace('đ', 'd', Str::ascii(Str::lower(trim($value))));
    }
}
