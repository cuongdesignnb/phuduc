<?php

namespace Tests\Feature\Admin;

use App\Models\Warranty;
use App\Services\Admin\Operations\WarrantyStatusService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarrantyEffectiveStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_effective_status_filter_parity_matches_dashboard_semantics(): void
    {
        $today = CarbonImmutable::today();
        $scheduled = Warranty::create(['serial_number' => 'EFFECTIVE-SCHEDULED', 'product_name' => 'Sắp kích hoạt', 'activation_date' => $today->addDay(), 'status' => 'active']);
        $active = Warranty::create(['serial_number' => 'EFFECTIVE-ACTIVE', 'product_name' => 'Đang hiệu lực', 'activation_date' => $today->subDay(), 'expiration_date' => $today->addDay(), 'status' => 'active']);
        $expired = Warranty::create(['serial_number' => 'EFFECTIVE-EXPIRED', 'product_name' => 'Đã hết hạn', 'expiration_date' => $today->subDay(), 'status' => 'active']);
        $voided = Warranty::create(['serial_number' => 'EFFECTIVE-VOIDED', 'product_name' => 'Đã hủy', 'status' => 'voided']);
        $statuses = app(WarrantyStatusService::class);

        $this->assertSame('scheduled', $statuses->effective($scheduled, $today));
        $this->assertSame('active', $statuses->effective($active, $today));
        $this->assertSame('expired', $statuses->effective($expired, $today));
        $this->assertSame('voided', $statuses->effective($voided, $today));
        $this->assertSame(1, $statuses->filter(Warranty::query(), 'active')->count());
        $this->assertSame(1, $statuses->activeForDashboard(Warranty::query())->count());
    }
}
