<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Warranty;
use App\Services\Admin\AdminConcurrencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarrantyRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_and_order_warranties_keep_server_owned_snapshots(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->post(route('admin.warranties.store'), ['mode' => 'manual', 'serial_number' => ' abc-001 ', 'product_name' => 'Ngoài web', 'customer_name' => 'Khách lẻ', 'customer_phone' => '+84900000000'])->assertRedirect();
        $manual = Warranty::firstOrFail();
        $this->assertSame('ABC-001', $manual->serial_number);
        $this->assertSame('0900000000', $manual->customer_phone);

        $product = Product::create(['name' => 'Sản phẩm web', 'slug' => 'warranty-product', 'price' => 100, 'stock' => 1, 'status' => 'active']);
        $order = Order::create(['order_number' => 'ORD-WARRANTY-ADMIN', 'customer_name' => 'Người mua', 'customer_phone' => '0901111111', 'total_amount' => 100, 'status' => 'completed']);
        $item = OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'product_name' => 'Snapshot web', 'price' => 100, 'quantity' => 1, 'total' => 100]);
        $this->actingAs($admin)->post(route('admin.warranties.store'), ['mode' => 'order', 'order_id' => $order->id, 'order_item_id' => $item->id, 'serial_number' => 'web-001'])->assertRedirect();
        $linked = Warranty::where('serial_number', 'WEB-001')->firstOrFail();
        $this->assertSame('Snapshot web', $linked->product_name);
        $this->assertSame('Người mua', $linked->customer_name);
        $this->assertSame('0901111111', $linked->customer_phone);
    }

    public function test_order_item_must_belong_to_selected_order_and_order_items_are_not_truncated(): void
    {
        $admin = User::factory()->admin()->create();
        $firstOrder = Order::create(['order_number' => 'ORD-WARRANTY-FIRST', 'customer_name' => 'Khách một', 'total_amount' => 0, 'status' => 'completed']);
        $secondOrder = Order::create(['order_number' => 'ORD-WARRANTY-SECOND', 'customer_name' => 'Khách hai', 'total_amount' => 0, 'status' => 'completed']);
        $foreignItem = OrderItem::create(['order_id' => $secondOrder->id, 'product_name' => 'Dòng khác', 'price' => 100, 'quantity' => 1, 'total' => 100]);

        $this->actingAs($admin)->post(route('admin.warranties.store'), ['mode' => 'order', 'order_id' => $firstOrder->id, 'order_item_id' => $foreignItem->id, 'serial_number' => 'ownership-001'])
            ->assertSessionHasErrors(['order_item_id' => 'Sản phẩm đã chọn không thuộc đơn hàng này.']);

        for ($index = 1; $index <= 51; $index++) {
            OrderItem::create(['order_id' => $firstOrder->id, 'product_name' => "Dòng {$index}", 'price' => 100, 'quantity' => 1, 'total' => 100]);
        }

        $response = $this->actingAs($admin)->getJson(route('admin.warranty-lookups.order-items', $firstOrder));
        $response->assertOk();
        $this->assertCount(51, $response->json('data'));
        $this->assertSame('Dòng 51', $response->json('data.50.product_name'));
    }

    public function test_voided_warranty_is_terminal_and_repeated_void_keeps_first_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $warranty = Warranty::create(['serial_number' => 'VOID-001', 'product_name' => 'Sản phẩm', 'status' => 'active']);
        $version = app(AdminConcurrencyService::class)->version($warranty);

        $this->actingAs($admin)->patch(route('admin.warranties.void', $warranty), ['version' => $version, 'reason' => 'Lý do đầu tiên'])->assertRedirect();
        $warranty->refresh();
        $firstVersion = app(AdminConcurrencyService::class)->version($warranty);
        $this->assertSame('Lý do đầu tiên', $warranty->void_reason);

        $this->actingAs($admin)->patch(route('admin.warranties.void', $warranty), ['version' => $firstVersion, 'reason' => 'Lý do thứ hai'])->assertRedirect();
        $this->assertSame('Lý do đầu tiên', $warranty->refresh()->void_reason);

        $this->actingAs($admin)->put(route('admin.warranties.update', $warranty), ['mode' => 'manual', 'serial_number' => 'VOID-002', 'product_name' => 'Đổi tên', 'customer_name' => 'Khách', 'customer_phone' => '0900000000', 'version' => $firstVersion])
            ->assertSessionHasErrors(['warranty' => 'Bảo hành đã hủy không thể chỉnh sửa.']);
        $this->assertSame('VOID-001', $warranty->refresh()->serial_number);
    }

    public function test_duplicate_serial_conflict_returns_vietnamese_validation_error(): void
    {
        $admin = User::factory()->admin()->create();
        Warranty::create(['serial_number' => 'DUP-001', 'product_name' => 'Đã có', 'status' => 'active']);

        $this->actingAs($admin)->post(route('admin.warranties.store'), ['mode' => 'manual', 'serial_number' => ' dup-001 ', 'product_name' => 'Mới', 'customer_name' => 'Khách', 'customer_phone' => '0900000000'])
            ->assertSessionHasErrors(['serial_number' => 'Mã serial này đã tồn tại.']);
    }
}
