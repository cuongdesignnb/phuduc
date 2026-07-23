<?php

namespace App\Services\Storefront;

use App\Models\Product;

class CartResolver
{
    public function __construct(private readonly CartSessionService $sessionCart) {}

    /** @return array{entries: list<array{product: Product, product_id: int, quantity: int}>, warnings: list<string>} */
    public function resolve(): array
    {
        $stored = $this->sessionCart->normalize();
        $ids = array_keys($stored);
        $products = $ids === []
            ? collect()
            : Product::query()->with('cardImage')->whereIn('id', $ids)->get()->keyBy('id');
        $canonical = [];
        $entries = [];
        $warnings = [];

        foreach ($stored as $productId => $storedEntry) {
            $product = $products->get($productId);

            if (! $product || $product->status !== 'active' || (int) $product->stock < 1 || (int) $product->price <= 0) {
                $warnings[] = 'Một sản phẩm trong giỏ hàng không còn khả dụng và đã được loại bỏ.';

                continue;
            }

            $quantity = min((int) $storedEntry['quantity'], (int) $product->stock, 99);
            if ($quantity < 1) {
                $warnings[] = 'Một sản phẩm trong giỏ hàng đã hết hàng và đã được loại bỏ.';

                continue;
            }

            if ($quantity !== (int) $storedEntry['quantity']) {
                $warnings[] = "Số lượng {$product->name} đã được điều chỉnh theo tồn kho hiện tại.";
            }

            $canonical[$productId] = ['quantity' => $quantity];
            $entries[] = ['product' => $product, 'product_id' => (int) $productId, 'quantity' => $quantity];
        }

        $this->sessionCart->put($canonical);

        return ['entries' => $entries, 'warnings' => $warnings];
    }

    public function product(int $productId): ?Product
    {
        return Product::query()->with('cardImage')->find($productId);
    }
}
