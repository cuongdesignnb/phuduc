<?php

namespace Tests\Feature\Storefront;

use App\Models\Warranty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WarrantyLookupParityTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_lookup_and_wrong_phone_are_private(): void
    {
        Warranty::create(['serial_number' => 'MANUAL-LOOKUP', 'product_name' => 'Sản phẩm', 'customer_name' => 'Riêng tư', 'customer_phone' => '0900000000', 'status' => 'active']);

        $this->post(route('warranty-lookup.lookup'), ['serial_number' => ' manual-lookup ', 'customer_phone' => '+84900000000'])->assertInertia(fn (Assert $page) => $page->where('page.lookup.result.serial_number', 'MANUAL-LOOKUP')->missing('page.lookup.result.customer_phone')->missing('page.lookup.result.void_reason'));
        $this->post(route('warranty-lookup.lookup'), ['serial_number' => 'MANUAL-LOOKUP', 'customer_phone' => '0901111111'])->assertInertia(fn (Assert $page) => $page->where('page.lookup.result', null)->where('page.lookup.message', 'Không tìm thấy thông tin bảo hành phù hợp với thông tin đã cung cấp.'));
    }
}
