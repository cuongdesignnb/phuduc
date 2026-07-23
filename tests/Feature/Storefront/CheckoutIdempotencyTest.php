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
            ->where('order.order_number', Order::first()->order_number)
            ->where('order.total_display', '900.000 ₫')
            ->missing('order.id')
            ->missing('order.public_token')
            ->missing('order.checkout_intent')
        );
        $this->get('/thanh-toan/thanh-cong/invalid-token')->assertNotFound();
    }

    private function checkoutData(?string $intent): array
    {
        return ['checkout_intent' => $intent, 'customer_name' => 'Synthetic Customer', 'customer_phone' => '0900000000', 'customer_email' => 'synthetic@example.test', 'shipping_address' => 'Synthetic address', 'notes' => 'QA'];
    }
}
