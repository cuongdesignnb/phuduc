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

        $this->post('/tra-cuu-don-hang', ['order_number' => ' ord-lookup ', 'customer_phone' => '090 000 0000'])->assertInertia(fn (Assert $page) => $page
            ->where('page.lookup.searched', true)
            ->where('page.lookup.result.order_number', 'ORD-LOOKUP')
            ->where('page.lookup.result.total', 100000)
            ->where('page.lookup.result.items.0.unit_price', 100000)
            ->missing('page.lookup.result.customer_name')
            ->missing('page.lookup.result.customer_email')
            ->missing('page.lookup.result.shipping_address')
        );

        $this->post('/tra-cuu-don-hang', ['order_number' => 'ORD-LOOKUP', 'customer_phone' => 'wrong'])->assertSessionHasErrors('customer_phone');
    }

    public function test_lookup_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->post('/tra-cuu-don-hang', ['order_number' => 'missing', 'customer_phone' => '0900000000'])->assertOk();
        }

        $this->post('/tra-cuu-don-hang', ['order_number' => 'missing', 'customer_phone' => '0900000000'])->assertStatus(429);
    }
}
