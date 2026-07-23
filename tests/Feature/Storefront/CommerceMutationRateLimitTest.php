<?php

namespace Tests\Feature\Storefront;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CommerceMutationRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_named_limits_are_applied_only_to_mutation_routes(): void
    {
        $this->assertContains('throttle:commerce-cart', Route::getRoutes()->getByName('cart.add')->middleware());
        $this->assertContains('throttle:commerce-cart', Route::getRoutes()->getByName('cart.clear')->middleware());
        $this->assertContains('throttle:commerce-checkout', Route::getRoutes()->getByName('checkout.store')->middleware());
        $this->assertContains('throttle:commerce-order-lookup', Route::getRoutes()->getByName('order-lookup.lookup')->middleware());
        $this->assertNotContains('throttle:commerce-cart', Route::getRoutes()->getByName('cart.index')->middleware());
        $this->assertNotContains('throttle:commerce-checkout', Route::getRoutes()->getByName('checkout.index')->middleware());
    }

    public function test_checkout_submit_is_rate_limited(): void
    {
        $product = Product::create(['name' => 'Checkout rate', 'slug' => 'checkout-rate', 'price' => 100000, 'stock' => 1, 'status' => 'active']);
        $this->withSession(['cart' => [$product->id => ['quantity' => 1]]])->get('/thanh-toan');

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->post('/thanh-toan', [
                'checkout_intent' => 'invalid-intent',
                'customer_name' => 'Customer',
                'customer_phone' => '0900000000',
                'shipping_address' => 'Address',
            ])->assertRedirect();
        }

        $this->post('/thanh-toan', [
            'checkout_intent' => 'invalid-intent',
            'customer_name' => 'Customer',
            'customer_phone' => '0900000000',
            'shipping_address' => 'Address',
        ])->assertStatus(429);
    }
}
