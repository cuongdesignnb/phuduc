<?php

namespace Tests\Feature\Storefront;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CheckoutSuccessPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_success_contract_masks_phone_and_omits_sensitive_fields(): void
    {
        $product = Product::create(['name' => 'Privacy item', 'slug' => 'privacy-item', 'price' => 100000, 'stock' => 1, 'status' => 'active']);
        $this->withSession(['cart' => [$product->id => ['quantity' => 1]]])->get('/thanh-toan');
        $response = $this->post('/thanh-toan', [
            'checkout_intent' => session('checkout_intent'),
            'customer_name' => 'Private Customer',
            'customer_phone' => '0900000000',
            'customer_email' => 'private@example.test',
            'shipping_address' => 'Private address',
        ]);

        $success = $this->get($response->headers->get('Location'));

        $success->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.order.customer.phone_masked', '09******00')
                ->where('page.seo.canonical', null)
                ->where('page.seo.robots', 'noindex, nofollow')
                ->missing('page.order.customer.phone')
                ->missing('page.order.shipping_address')
                ->missing('page.order.customer_email')
                ->missing('page.order.checkout_intent')
                ->missing('page.order.public_token')
                ->missing('page.order.id')
                ->missing('page.seo.public_token')
                ->missing('page.seo.og:url')
            );
        $this->assertStringContainsString('private', $success->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $success->headers->get('Cache-Control'));
        $this->assertSame('0900000000', Order::first()->customer_phone);
    }
}
