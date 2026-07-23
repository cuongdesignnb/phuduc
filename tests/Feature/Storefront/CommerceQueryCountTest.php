<?php

namespace Tests\Feature\Storefront;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Warranty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CommerceQueryCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_commerce_pages_do_not_scale_queries_with_cart_or_item_count(): void
    {
        $this->createProducts(1, 'query-one');
        $cartOne = $this->warmAndCount('/gio-hang', ['cart' => $this->cart()]);
        $checkoutOne = $this->warmAndCount('/thanh-toan', ['cart' => $this->cart()]);

        Product::query()->delete();
        $this->createProducts(12, 'query-many');
        $cartMany = $this->warmAndCount('/gio-hang', ['cart' => $this->cart()]);
        $checkoutMany = $this->warmAndCount('/thanh-toan', ['cart' => $this->cart()]);

        $this->record(['CART_1' => $cartOne, 'CART_12' => $cartMany, 'CHECKOUT_1' => $checkoutOne, 'CHECKOUT_12' => $checkoutMany]);
        $this->assertLessThanOrEqual($cartOne + 1, $cartMany);
        $this->assertLessThanOrEqual($checkoutOne + 1, $checkoutMany);
    }

    public function test_success_order_and_lookup_queries_are_bounded(): void
    {
        $one = $this->createOrder('query-order-one', 1);
        $successOne = $this->warmAndCount('/thanh-toan/thanh-cong/'.$one->public_token);
        $lookupOne = $this->warmAndCount('/tra-cuu-don-hang', [], 'post', ['order_number' => $one->order_number, 'customer_phone' => '0900000000']);

        OrderItem::query()->delete();
        $many = $this->createOrder('query-order-many', 12);
        $successMany = $this->warmAndCount('/thanh-toan/thanh-cong/'.$many->public_token);
        $lookupMany = $this->warmAndCount('/tra-cuu-don-hang', [], 'post', ['order_number' => $many->order_number, 'customer_phone' => '0900000000']);

        $this->record(['SUCCESS_1' => $successOne, 'SUCCESS_12' => $successMany, 'ORDER_LOOKUP' => $lookupOne, 'ORDER_LOOKUP_MANY' => $lookupMany]);
        $this->assertLessThanOrEqual($successOne + 1, $successMany);
        $this->assertLessThanOrEqual($lookupOne + 1, $lookupMany);
    }

    public function test_warranty_lookup_uses_one_bounded_query(): void
    {
        $order = $this->createOrder('query-warranty', 1);
        Warranty::create(['order_id' => $order->id, 'serial_number' => 'QUERY-SERIAL', 'product_name' => 'Query product', 'status' => 'active']);
        $count = $this->warmAndCount('/tra-cuu-bao-hanh', [], 'post', ['serial_number' => 'QUERY-SERIAL', 'customer_phone' => '0900000000']);

        $this->record(['WARRANTY_LOOKUP' => $count]);
        $this->assertLessThanOrEqual(12, $count);
    }

    private function warmAndCount(string $uri, array $session = [], string $method = 'get', array $data = []): int
    {
        $this->withSession($session)->{$method}($uri, $data)->assertOk();
        DB::flushQueryLog();
        DB::enableQueryLog();

        try {
            $this->withSession($session)->{$method}($uri, $data)->assertOk();

            return collect(DB::getQueryLog())->reject(fn (array $query) => str_starts_with(strtolower($query['query'] ?? ''), 'pragma '))->count();
        } finally {
            DB::disableQueryLog();
            DB::flushQueryLog();
        }
    }

    private function createProducts(int $count, string $prefix): void
    {
        for ($i = 1; $i <= $count; $i++) {
            Product::create(['name' => "Query product {$i}", 'slug' => "{$prefix}-{$i}", 'price' => 100000, 'stock' => 20, 'status' => 'active']);
        }
    }

    private function cart(): array
    {
        return Product::query()->pluck('id')->mapWithKeys(fn (int $id) => [$id => ['quantity' => 1]])->all();
    }

    private function createOrder(string $number, int $items): Order
    {
        $order = Order::create(['order_number' => strtoupper($number), 'public_token' => str_repeat($number, 4), 'checkout_intent' => 'intent-'.$number, 'customer_name' => 'Synthetic', 'customer_phone' => '0900000000', 'total_amount' => 100000 * $items, 'status' => 'pending']);
        for ($i = 1; $i <= $items; $i++) {
            OrderItem::create(['order_id' => $order->id, 'product_name' => "Item {$i}", 'price' => 100000, 'quantity' => 1, 'total' => 100000]);
        }

        return $order;
    }

    private function record(array $counts): void
    {
        foreach ($counts as $key => $value) {
            fwrite(STDERR, $key.'='.$value.PHP_EOL);
        }
    }
}
