<?php

namespace Tests\Feature\Storefront;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutStockIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_price_and_name_are_used_and_stock_is_decremented(): void
    {
        $product = Product::create(['name' => 'Current name', 'slug' => 'current-name', 'price' => 1234567, 'stock' => 4, 'status' => 'active']);
        $this->withSession(['cart' => [$product->id => ['quantity' => 2, 'price' => 1, 'name' => 'Tampered']]])->get('/thanh-toan');
        $this->post('/thanh-toan', ['checkout_intent' => session('checkout_intent'), 'customer_name' => 'Customer', 'customer_phone' => '0900000000', 'shipping_address' => 'Address'])->assertRedirect();

        $order = Order::with('items')->first();
        $this->assertSame(2469134, (int) $order->total_amount);
        $this->assertSame('Current name', $order->items->first()->product_name);
        $this->assertSame(1234567, (int) $order->items->first()->price);
        $this->assertSame(2, Product::find($product->id)->stock);
    }

    public function test_insufficient_stock_fails_without_clearing_cart_or_creating_order(): void
    {
        $product = Product::create(['name' => 'Limited', 'slug' => 'limited', 'price' => 100000, 'stock' => 1, 'status' => 'active']);
        $this->withSession(['cart' => [$product->id => ['quantity' => 2]]])->get('/thanh-toan');
        session()->put('cart', [$product->id => ['quantity' => 2]]);
        $this->post('/thanh-toan', ['checkout_intent' => session('checkout_intent'), 'customer_name' => 'Customer', 'customer_phone' => '0900000000', 'shipping_address' => 'Address'])->assertSessionHasErrors('cart');
        $this->assertSame(0, Order::count());
        $this->assertSame(1, Product::find($product->id)->stock);
    }
}
