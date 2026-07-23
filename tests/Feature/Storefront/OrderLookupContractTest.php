<?php

namespace Tests\Feature\Storefront;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OrderLookupContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_lookup_requires_two_factors_and_returns_bounded_contract(): void
    {
        $order = Order::create(['order_number' => 'ORD-LOOKUP', 'customer_name' => 'Private', 'customer_phone' => '0900000000', 'customer_email' => 'private@example.test', 'shipping_address' => 'Private address', 'total_amount' => 100000, 'status' => 'pending']);
        OrderItem::create(['order_id' => $order->id, 'product_name' => 'Item', 'price' => 100000, 'quantity' => 1, 'total' => 100000]);

        $this->post('/tra-cuu-don-hang', ['order_number' => 'ORD-LOOKUP', 'customer_phone' => 'wrong'])->assertInertia(fn (Assert $page) => $page->where('searched', true)->where('message', 'Không tìm thấy đơn hàng phù hợp với thông tin đã cung cấp.')->missing('order.customer_name'));
        $this->post('/tra-cuu-don-hang', ['order_number' => 'ORD-LOOKUP', 'customer_phone' => '0900000000'])->assertHeader('Cache-Control', 'no-store, private')->assertInertia(fn (Assert $page) => $page
            ->where('order.order_number', 'ORD-LOOKUP')
            ->where('order.total_display', '100.000 ₫')
            ->missing('order.id')
            ->missing('order.customer_email')
            ->missing('order.shipping_address')
        );
    }

    public function test_lookup_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->post('/tra-cuu-don-hang', ['order_number' => 'missing', 'customer_phone' => '0900000000'])->assertOk();
        }

        $this->post('/tra-cuu-don-hang', ['order_number' => 'missing', 'customer_phone' => '0900000000'])->assertStatus(429);
    }
}
