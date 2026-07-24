<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardDatabasePortabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_revenue_contract_works_on_the_sqlite_baseline_without_mysql_date_format(): void
    {
        $page = app(AdminDashboardService::class)->page(User::factory()->admin()->create());

        $this->assertSame('sqlite', config('database.default'));
        $this->assertCount(6, $page['page']['dashboard']['monthly_revenue']);
        $this->assertSame(0, $page['page']['dashboard']['monthly_revenue'][0]['revenue']);
    }
}
