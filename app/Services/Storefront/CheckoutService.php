<?php

namespace App\Services\Storefront;

use App\Models\Order;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly CartSessionService $sessionCart,
        private readonly CheckoutIntentService $intents,
    ) {}

    public function checkout(array $data, string $intent): Order
    {
        $created = false;

        try {
            $order = DB::transaction(function () use ($data, $intent, &$created): Order {
                $existing = Order::query()->where('checkout_intent', $intent)->first();
                if ($existing) {
                    return $existing->load('items');
                }

                if (! $this->intents->isActive($intent)) {
                    throw ValidationException::withMessages(['checkout_intent' => 'Phiên thanh toán không hợp lệ. Vui lòng tải lại trang.']);
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

                $created = true;

                return $order->load('items');
            });
        } catch (UniqueConstraintViolationException $exception) {
            if (! $this->isCheckoutIntentConflict($exception)) {
                throw $exception;
            }

            $order = Order::query()->with('items')->where('checkout_intent', $intent)->first();
            if (! $order) {
                throw $exception;
            }
        } catch (QueryException $exception) {
            if (! $this->isCheckoutIntentConflict($exception)) {
                throw $exception;
            }

            $order = Order::query()->with('items')->where('checkout_intent', $intent)->first();
            if (! $order) {
                throw $exception;
            }
        }

        if ($created) {
            $this->sessionCart->clear();
            $this->intents->consume($intent, (string) $order->public_token);
        }

        return $order;
    }

    private function isCheckoutIntentConflict(QueryException $exception): bool
    {
        if ($exception instanceof UniqueConstraintViolationException) {
            return preg_match('/checkout_intent/i', $exception->getMessage()) === 1;
        }

        $sqlState = $exception->errorInfo[0] ?? $exception->getCode();

        return (string) $sqlState === '23000'
            && preg_match('/checkout_intent/i', $exception->getMessage()) === 1;
    }
}
