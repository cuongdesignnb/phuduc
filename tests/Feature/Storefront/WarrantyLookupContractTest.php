<?php

namespace Tests\Feature\Storefront;

use App\Models\Order;
use App\Models\Warranty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WarrantyLookupContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_warranty_lookup_requires_serial_and_customer_phone(): void
    {
        $order = Order::create(['order_number' => 'ORD-WARRANTY', 'customer_phone' => '0900000000', 'total_amount' => 0, 'status' => 'pending']);
        Warranty::create(['order_id' => $order->id, 'serial_number' => 'SERIAL-1', 'product_name' => 'Product', 'status' => 'active']);

        $this->post('/tra-cuu-bao-hanh', ['serial_number' => 'SERIAL-1', 'customer_phone' => 'wrong'])->assertInertia(fn (Assert $page) => $page->where('message', 'Không tìm thấy thông tin bảo hành phù hợp với thông tin đã cung cấp.'));
        $this->post('/tra-cuu-bao-hanh', ['serial_number' => 'SERIAL-1', 'customer_phone' => '0900000000'])->assertHeader('Pragma', 'no-cache')->assertInertia(fn (Assert $page) => $page
            ->where('warranty.serial_number', 'SERIAL-1')
            ->where('warranty.status_display', 'Còn hiệu lực')
            ->missing('warranty.id')
            ->missing('warranty.order_id')
        );
    }
}
