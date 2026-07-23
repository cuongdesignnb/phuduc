<?php

namespace App\Services\Storefront;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(private readonly CartSessionService $sessionCart) {}

    public function checkout(array $data, string $intent): Order
    {
        $order = DB::transaction(function () use ($data, $intent): Order {
            $existing = Order::query()->where('checkout_intent', $intent)->first();
            if ($existing) {
                return $existing->load('items');
            }

            $cart = $this->sessionCart->normalize();
            if ($cart === []) {
                throw ValidationException::withMessages(['cart' => 'Giỏ hàng trống.']);
            }

            $ids = array_keys($cart);
            sort($ids, SORT_NUMERIC);
            $products = DB::table('products')->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $lines = [];
            $total = 0;

            foreach ($ids as $id) {
                $product = $products->get($id);
                $quantity = (int) $cart[$id]['quantity'];

                if (! $product || $product->status !== 'active' || (int) $product->price <= 0) {
                    throw ValidationException::withMessages(['cart' => 'Một sản phẩm trong giỏ hàng không còn khả dụng.']);
                }
                if ((int) $product->stock < $quantity) {
                    throw ValidationException::withMessages(['cart' => "Sản phẩm {$product->name} không đủ số lượng tồn kho."]);
                }

                $unitPrice = (int) round((float) $product->price);
                $lineTotal = $unitPrice * $quantity;
                $total += $lineTotal;
                $lines[] = ['product' => $product, 'quantity' => $quantity, 'price' => $unitPrice, 'total' => $lineTotal];
            }

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'checkout_intent' => $intent,
                'public_token' => Str::random(64),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'shipping_address' => $data['shipping_address'],
                'total_amount' => $total,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'price' => $line['price'],
                    'quantity' => $line['quantity'],
                    'total' => $line['total'],
                ]);

                DB::table('products')->where('id', $line['product']->id)->decrement('stock', $line['quantity']);
            }

            return $order->load('items');
        });

        $this->sessionCart->clear();

        return $order;
    }
}
