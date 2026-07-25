<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Warranty;
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
}
