<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\Admin\AdminConcurrencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_order_can_move_to_processing_and_then_shipping(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::create(['order_number' => 'ORD-TRANSITION', 'customer_name' => 'Khách', 'total_amount' => 0, 'status' => 'pending']);
        $version = app(AdminConcurrencyService::class)->version($order);

        $this->actingAs($admin)->patch(route('admin.orders.updateStatus', $order), ['status' => 'processing', 'version' => $version])->assertRedirect();
        $order->refresh();
        $this->assertSame('processing', $order->status);
        $this->assertDatabaseHas('order_status_histories', ['order_id' => $order->id, 'from_status' => 'pending', 'to_status' => 'processing']);

        $this->actingAs($admin)->patch(route('admin.orders.updateStatus', $order), ['status' => 'shipping', 'version' => app(AdminConcurrencyService::class)->version($order)])->assertRedirect();
        $this->assertSame('shipping', $order->refresh()->status);
    }

    public function test_cancellation_restores_stock_once_and_requires_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::create(['name' => 'Sản phẩm', 'slug' => 'stock-order', 'price' => 100, 'stock' => 2, 'status' => 'active']);
        $order = Order::create(['order_number' => 'ORD-CANCEL', 'customer_name' => 'Khách', 'total_amount' => 100, 'status' => 'pending']);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'product_name' => 'Sản phẩm', 'price' => 100, 'quantity' => 3, 'total' => 300]);
        $version = app(AdminConcurrencyService::class)->version($order);

        $this->actingAs($admin)->patch(route('admin.orders.updateStatus', $order), ['status' => 'cancelled', 'version' => $version])->assertSessionHasErrors('reason');
        $this->actingAs($admin)->patch(route('admin.orders.updateStatus', $order), ['status' => 'cancelled', 'version' => $version, 'reason' => 'Khách yêu cầu hủy'])->assertRedirect();
        $this->assertSame(5, (int) $product->refresh()->stock);
        $this->assertDatabaseCount('order_status_histories', 1);

        $this->actingAs($admin)->patch(route('admin.orders.updateStatus', $order), ['status' => 'cancelled', 'version' => app(AdminConcurrencyService::class)->version($order), 'reason' => 'Gửi lại'])->assertRedirect();
        $this->assertSame(5, (int) $product->refresh()->stock);
        $this->assertDatabaseCount('order_status_histories', 1);
    }

    public function test_stale_order_status_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::create(['order_number' => 'ORD-STALE', 'total_amount' => 0, 'status' => 'pending']);

        $this->actingAs($admin)->patch(route('admin.orders.updateStatus', $order), ['status' => 'processing', 'version' => 'stale-version'])->assertSessionHasErrors('version');
        $this->assertSame('pending', $order->refresh()->status);
    }
}
