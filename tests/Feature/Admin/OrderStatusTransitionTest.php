<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\Admin\AdminConcurrencyService;
use App\Services\Admin\Operations\OrderStatusTransitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_invalid_terminal_transitions_and_unresolved_stock_are_explicit(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::create(['order_number' => 'ORD-INVALID', 'total_amount' => 0, 'status' => 'pending']);
        $version = app(AdminConcurrencyService::class)->version($order);

        $this->actingAs($admin)->patch(route('admin.orders.updateStatus', $order), ['status' => 'shipping', 'version' => $version])->assertSessionHasErrors('status');
        OrderItem::create(['order_id' => $order->id, 'product_id' => null, 'product_name' => 'Snapshot không còn liên kết', 'price' => 100, 'quantity' => 1, 'total' => 100]);
        $this->actingAs($admin)->patch(route('admin.orders.updateStatus', $order), ['status' => 'cancelled', 'version' => $version, 'reason' => 'Không còn nhu cầu'])->assertRedirect()->assertSessionHas('warning');

        $cancelled = Order::create(['order_number' => 'ORD-CANCELLED', 'total_amount' => 0, 'status' => 'cancelled']);
        $this->actingAs($admin)->patch(route('admin.orders.updateStatus', $cancelled), ['status' => 'pending', 'version' => app(AdminConcurrencyService::class)->version($cancelled)])->assertSessionHasErrors('status');
    }

    public function test_real_concurrent_cancellation_restores_stock_once(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! function_exists('proc_open')) {
            $this->markTestSkipped('Real concurrent cancellation requires isolated MySQL with a process runner.');
        }

        $admin = User::factory()->admin()->create();
        $product = Product::create(['name' => 'Concurrent product', 'slug' => 'concurrent-cancellation-product', 'price' => 100, 'stock' => 0, 'status' => 'active']);
        $order = Order::create(['order_number' => 'ORD-CONCURRENT-CANCEL', 'customer_name' => 'Khách', 'total_amount' => 100, 'status' => 'pending']);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'product_name' => 'Concurrent product', 'price' => 100, 'quantity' => 2, 'total' => 200]);
        $version = app(AdminConcurrencyService::class)->version($order);
        $readyFile = tempnam(sys_get_temp_dir(), 'pr3c-concurrent-ready-');
        $resultFile = tempnam(sys_get_temp_dir(), 'pr3c-concurrent-result-');
        $command = [PHP_BINARY, base_path('tests/Support/Pr3cConcurrentCancellationWorker.php'), (string) $order->id, (string) $admin->id, $version, $readyFile, $resultFile];
        $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $deadline = microtime(true) + 10;
        while (! file_exists($readyFile) && microtime(true) < $deadline) {
            usleep(10000);
        }
        app(OrderStatusTransitionService::class)->transition(Order::findOrFail($order->id), $admin, ['status' => 'cancelled', 'version' => $version, 'reason' => 'Concurrent parent']);
        proc_close($process);
        $childResult = file_get_contents($resultFile);
        @unlink($readyFile);
        @unlink($resultFile);

        $this->assertSame('cancelled', $order->refresh()->status);
        $this->assertSame(2, (int) $product->refresh()->stock);
        $this->assertDatabaseCount('order_status_histories', 1);
        $this->assertNotSame('success', $childResult);
    }
}
