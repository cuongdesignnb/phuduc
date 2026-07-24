<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_dtos_do_not_expose_checkout_or_customer_contact_fields(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::create(['name' => 'Private product', 'slug' => 'private-product', 'price' => 100000, 'stock' => 2, 'status' => 'active']);
        $order = Order::create([
            'order_number' => 'ORD-PRIVACY', 'customer_name' => 'Private customer', 'customer_phone' => '0900000000',
            'customer_email' => 'private@example.test', 'checkout_intent' => 'intent', 'public_token' => 'token',
            'shipping_address' => 'Private address', 'total_amount' => 100000, 'status' => 'pending',
        ]);
        Review::create([
            'product_id' => $product->id, 'customer_name' => 'Reviewer', 'customer_phone' => '0900000000',
            'customer_email' => 'reviewer@example.test', 'content' => 'Good', 'rating' => 5, 'status' => 'pending',
        ]);

        $page = app(AdminDashboardService::class)->page($admin)['page']['dashboard'];
        $orderDto = collect($page['recent_orders'])->firstWhere('id', $order->id);
        $reviewDto = $page['recent_reviews'][0];

        $this->assertArrayNotHasKey('public_token', $orderDto);
        $this->assertArrayNotHasKey('checkout_intent', $orderDto);
        $this->assertArrayNotHasKey('customer_phone', $orderDto);
        $this->assertArrayNotHasKey('customer_email', $orderDto);
        $this->assertArrayNotHasKey('content', $reviewDto);
        $this->assertArrayNotHasKey('customer_phone', $reviewDto);
        $this->assertArrayNotHasKey('customer_email', $reviewDto);
    }
}
