<?php

namespace App\Services\Storefront;

use App\Models\Product;

class CartPresentationService
{
    public function __construct(private readonly MediaUrlService $mediaUrl) {}

    /** @param array{product: Product, product_id: int, quantity: int} $entry */
    public function item(array $entry): array
    {
        $product = $entry['product'];
        $unitPrice = (int) round((float) $product->price);
        $lineTotal = $unitPrice * $entry['quantity'];

        return [
            'product_id' => $entry['product_id'],
            'name' => $product->name,
            'slug' => $product->slug,
            'image_url' => $this->mediaUrl->resolve($product->cardImage?->image_path),
            'quantity' => $entry['quantity'],
            'stock' => (int) $product->stock,
            'unit_price_display' => $this->money($unitPrice),
            'line_total_display' => $this->money($lineTotal),
        ];
    }

    /** @param list<array{product: Product, product_id: int, quantity: int}> $entries */
    public function cart(array $entries): array
    {
        $items = array_map(fn (array $entry) => $this->item($entry), $entries);
        $total = collect($items)->sum(fn (array $item) => $this->integerMoney($item['line_total_display']));

        return [
            'items' => $items,
            'summary' => [
                'item_count' => collect($items)->sum('quantity'),
                'total_display' => $this->money($total),
            ],
        ];
    }

    public function money(int $amount): string
    {
        return number_format($amount, 0, ',', '.').' ₫';
    }

    private function integerMoney(string $display): int
    {
        return (int) preg_replace('/[^0-9]/', '', $display);
    }
}
