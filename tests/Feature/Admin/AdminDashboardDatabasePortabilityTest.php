<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use App\Services\Admin\AdminDashboardService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardDatabasePortabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_revenue_contract_works_on_the_sqlite_baseline_without_mysql_date_format(): void
    {
        $page = app(AdminDashboardService::class)->page(User::factory()->admin()->create());

        $this->assertContains(config('database.default'), ['sqlite', 'mysql']);
        $this->assertCount(6, $page['page']['dashboard']['monthly_revenue']);
        $this->assertSame(0, $page['page']['dashboard']['monthly_revenue'][0]['revenue']);
    }

    public function test_revenue_is_exact_zero_filled_and_cancelled_orders_are_excluded(): void
    {
        $admin = User::factory()->admin()->create();
        $now = CarbonImmutable::now()->startOfMonth();

        for ($offset = 0; $offset < 12; $offset++) {
            $order = Order::create([
                'order_number' => 'ORD-PORTABILITY-'.$offset,
                'customer_name' => 'Portability customer',
                'total_amount' => 100000 * ($offset + 1),
                'status' => $offset === 1 ? 'cancelled' : 'completed',
            ]);
            $date = $now->subMonths($offset)->addDay();
            $order->forceFill(['created_at' => $date, 'updated_at' => $date])->save();
        }

        $months = app(AdminDashboardService::class)->page($admin)['page']['dashboard']['monthly_revenue'];
        $values = array_column($months, 'revenue');
        $monthKeys = array_column($months, 'month');
        $sortedMonthKeys = $monthKeys;
        sort($sortedMonthKeys);

        $this->assertCount(6, $months);
        $this->assertSame($sortedMonthKeys, $monthKeys);
        $this->assertSame(1900000, array_sum($values));
        $this->assertSame(0, $values[4]);
        $this->assertContains(config('database.default'), ['sqlite', 'mysql']);
    }
}
