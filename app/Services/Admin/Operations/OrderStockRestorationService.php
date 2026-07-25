<?php

namespace App\Services\Admin\Operations;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class OrderStockRestorationService
{
    /**
     * @return array<int, array<string, int|null>>
     */
    public function restore(Order $order): array
    {
        $items = OrderItem::query()
            ->where('order_id', $order->id)
            ->orderBy('id')
            ->lockForUpdate()
            ->get(['id', 'product_id', 'quantity']);
        $productIds = $items->pluck('product_id')->filter()->map(fn ($id): int => (int) $id)->unique()->sort()->values();
        $products = $productIds->isEmpty()
            ? collect()
            : Product::query()->whereKey($productIds->all())->orderBy('id')->lockForUpdate()->get()->keyBy('id');
        $unresolved = [];

        foreach ($items as $item) {
            $productId = $item->product_id !== null ? (int) $item->product_id : null;
            $product = $productId !== null ? $products->get($productId) : null;
            if (! $product) {
                $unresolved[] = ['order_item_id' => (int) $item->id, 'product_id' => $productId];

                continue;
            }

            $product->increment('stock', (int) $item->quantity);
        }

        if ($unresolved !== []) {
            Log::warning('Order cancellation has unresolved stock lines.', [
                'order_id' => $order->id,
                'order_item_ids' => array_column($unresolved, 'order_item_id'),
            ]);
        }

        return $unresolved;
    }
}
