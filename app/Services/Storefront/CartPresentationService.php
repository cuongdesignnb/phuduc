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
            'max_quantity' => min((int) $product->stock, 99),
            'unit_price' => $unitPrice,
            'unit_price_display' => $this->money($unitPrice),
            'subtotal' => $lineTotal,
            'subtotal_display' => $this->money($lineTotal),
        ];
    }

    /** @param list<array{product: Product, product_id: int, quantity: int}> $entries */
    public function cart(array $entries): array
    {
        $items = array_map(fn (array $entry) => $this->item($entry), $entries);
        $total = collect($items)->sum('subtotal');
        $quantityCount = collect($items)->sum('quantity');

        return [
            'items' => $items,
            'summary' => [
                'item_count' => count($items),
                'quantity_count' => $quantityCount,
                'subtotal' => $total,
                'subtotal_display' => $this->money($total),
                'total' => $total,
                'total_display' => $this->money($total),
            ],
            'can_checkout' => $items !== [],
        ];
    }

    public function money(int $amount): string
    {
        return number_format($amount, 0, ',', '.').' ₫';
    }
}
