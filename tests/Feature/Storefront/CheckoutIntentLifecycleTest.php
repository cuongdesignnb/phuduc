<?php

namespace Tests\Feature\Storefront;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutIntentLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_consumed_intent_rotates_for_a_new_cart_and_old_duplicate_does_not_clear_it(): void
    {
        $firstProduct = $this->product('first', 2);
        $secondProduct = $this->product('second', 2);
        $newCartProduct = $this->product('new-cart', 2);

        $this->withSession(['cart' => [$firstProduct->id => ['quantity' => 1]]])->get('/thanh-toan')->assertOk();
        $firstIntent = session('checkout_intent');
        $firstData = $this->checkoutData($firstIntent, 'First Customer');
        $firstResponse = $this->post('/thanh-toan', $firstData)->assertRedirect();
        $firstToken = session('checkout_intent_consumed_order_token');

        $this->assertNotEmpty($firstToken);
        $this->assertSame(route('checkout.success', ['token' => $firstToken]), $firstResponse->headers->get('Location'));
        $this->assertSame(1, Order::count());

        $this->post('/gio-hang/add', ['product_id' => $secondProduct->id])->assertRedirect();
        $this->get('/thanh-toan')->assertOk();
        $secondIntent = session('checkout_intent');

        $this->assertNotSame($firstIntent, $secondIntent);
        $this->post('/thanh-toan', $this->checkoutData($secondIntent, 'Second Customer'))->assertRedirect();
        $this->assertSame(2, Order::count());
        $this->assertSame(1, Product::find($firstProduct->id)->stock);
        $this->assertSame(1, Product::find($secondProduct->id)->stock);

        $this->post('/gio-hang/add', ['product_id' => $newCartProduct->id])->assertRedirect();
        $this->post('/thanh-toan', $firstData)->assertRedirect();

        $this->assertSame(['quantity' => 1], session()->get('cart.'.$newCartProduct->id));
        $this->assertSame(2, Order::count());
    }

    private function product(string $slug, int $stock): Product
    {
        return Product::create(['name' => $slug, 'slug' => $slug, 'price' => 100000, 'stock' => $stock, 'status' => 'active']);
    }

    private function checkoutData(?string $intent, string $name): array
    {
        return ['checkout_intent' => $intent, 'customer_name' => $name, 'customer_phone' => '0900000000', 'shipping_address' => 'Address'];
    }
}
