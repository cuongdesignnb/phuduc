<?php

namespace Tests\Feature\Storefront;

use App\Models\Order;
use App\Models\Product;
use App\Models\Warranty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommercePhoneNormalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_normalizes_phone_email_and_text_fields_before_storage(): void
    {
        $product = Product::create(['name' => 'Normalize item', 'slug' => 'normalize-item', 'price' => 100000, 'stock' => 1, 'status' => 'active']);
        $this->withSession(['cart' => [$product->id => ['quantity' => 1]]])->get('/thanh-toan');

        $this->post('/thanh-toan', [
            'checkout_intent' => session('checkout_intent'),
            'customer_name' => '  Customer  ',
            'customer_phone' => '090 000-0000',
            'customer_email' => ' Customer@Example.TEST ',
            'shipping_address' => '  Address  ',
            'notes' => '  Note  ',
        ])->assertRedirect();

        $order = Order::first();
        $this->assertSame('0900000000', $order->customer_phone);
        $this->assertSame('customer@example.test', $order->customer_email);
        $this->assertSame('Customer', $order->customer_name);
        $this->assertSame('Address', $order->shipping_address);
        $this->assertSame('Note', $order->notes);
    }

    public function test_order_and_warranty_lookup_accept_plus84_and_84_forms(): void
    {
        $order = Order::create(['order_number' => 'ORD-NORMALIZE', 'customer_phone' => '0900000000', 'total_amount' => 0, 'status' => 'pending']);
        Warranty::create(['order_id' => $order->id, 'serial_number' => 'NORMALIZE-SERIAL', 'product_name' => 'Product', 'status' => 'active']);

        $this->post('/tra-cuu-don-hang', ['order_number' => 'ord-normalize', 'customer_phone' => '+84900000000'])->assertOk();
        $this->post('/tra-cuu-bao-hanh', ['serial_number' => 'normalize-serial', 'customer_phone' => '84900000000'])->assertOk();
    }

    public function test_invalid_phone_is_rejected_with_a_validation_error(): void
    {
        $this->post('/tra-cuu-don-hang', ['order_number' => 'ORD-INVALID', 'customer_phone' => '1234'])
            ->assertSessionHasErrors('customer_phone');
    }
}
