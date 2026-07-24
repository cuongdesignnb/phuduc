<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminDashboardQueryCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_service_stays_within_the_query_budget(): void
    {
        $admin = User::factory()->admin()->create();
        $queries = 0;
        DB::listen(function () use (&$queries): void {
            $queries++;
        });

        app(AdminDashboardService::class)->page($admin);

        $this->assertLessThanOrEqual(12, $queries, "Dashboard query count was {$queries}.");
    }
}
