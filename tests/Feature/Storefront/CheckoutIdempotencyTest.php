<?php

namespace Tests\Feature\Storefront;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CheckoutIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_checkout_intent_creates_one_order_and_decrements_stock_once(): void
    {
        $product = Product::create(['name' => 'Atomic item', 'slug' => 'atomic-item', 'price' => 1500000, 'stock' => 5, 'status' => 'active']);
        $this->withSession(['cart' => [$product->id => ['quantity' => 2]]])->get('/thanh-toan')->assertOk();
        $intent = session('checkout_intent');
        $data = $this->checkoutData($intent);

        $first = $this->post('/thanh-toan', $data)->assertRedirect();
        $second = $this->post('/thanh-toan', $data)->assertRedirect();

        $this->assertSame($first->headers->get('Location'), $second->headers->get('Location'));
        $this->assertSame(1, Order::count());
        $this->assertSame(3, Product::find($product->id)->stock);
        $this->assertSame([], session('cart', []));
        $this->assertSame($first->headers->get('Location'), route('checkout.success', ['token' => session('checkout_intent_consumed_order_token')]));
    }

    public function test_success_uses_opaque_token_and_safe_backend_contract(): void
    {
        $product = Product::create(['name' => 'Success item', 'slug' => 'success-item', 'price' => 900000, 'stock' => 2, 'status' => 'active']);
        $this->withSession(['cart' => [$product->id => ['quantity' => 1]]])->get('/thanh-toan');
        $response = $this->post('/thanh-toan', $this->checkoutData(session('checkout_intent')));
        $location = $response->headers->get('Location');

        $this->assertStringNotContainsString('/thanh-cong/'.$product->id, $location);
        $this->get($location)->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Guest/CheckoutSuccess')
            ->where('page.type', 'checkout_success')
            ->where('page.order.order_number', Order::first()->order_number)
            ->where('page.order.total', 900000)
            ->where('page.order.total_display', '900.000 ₫')
            ->where('page.order.customer.phone_masked', '09******00')
            ->where('page.order.items.0.unit_price', 900000)
            ->where('page.order.items.0.subtotal', 900000)
            ->missing('page.order.customer.phone')
            ->missing('page.order.shipping_address')
            ->missing('page.order.email')
            ->missing('page.order.public_token')
            ->missing('page.order.checkout_intent')
            ->missing('page.order.id')
        );
        $this->get('/thanh-toan/thanh-cong/invalid-token')->assertNotFound();
    }

    private function checkoutData(?string $intent): array
    {
        return ['checkout_intent' => $intent, 'customer_name' => '  Synthetic Customer  ', 'customer_phone' => '+84 900-000-000', 'customer_email' => ' Synthetic@Example.Test ', 'shipping_address' => '  Synthetic address  ', 'notes' => '  QA  '];
    }
}
