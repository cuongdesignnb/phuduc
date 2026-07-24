<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Warranty;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminDashboardQueryCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_one_and_twelve_record_dashboards_stay_within_budget_without_n_plus_one(): void
    {
        $admin = User::factory()->admin()->create();
        $service = app(AdminDashboardService::class);

        $service->page($admin);
        $empty = $this->measure($service, $admin);

        $this->createRecords(1, 'one');
        $one = $this->measure($service, $admin);

        $this->createRecords(11, 'twelve');
        $twelve = $this->measure($service, $admin);

        fwrite(STDOUT, PHP_EOL.'DASHBOARD_EMPTY='.$empty.PHP_EOL);
        fwrite(STDOUT, 'DASHBOARD_1='.$one.PHP_EOL);
        fwrite(STDOUT, 'DASHBOARD_12='.$twelve.PHP_EOL);
        fwrite(STDOUT, 'QUERY_BUDGET=12'.PHP_EOL);
        fwrite(STDOUT, 'N_PLUS_ONE='.($twelve <= $one + 1 ? 'NO' : 'YES').PHP_EOL);

        $this->assertLessThanOrEqual(12, $empty);
        $this->assertLessThanOrEqual(12, $one);
        $this->assertLessThanOrEqual(12, $twelve);
        $this->assertLessThanOrEqual($one + 1, $twelve);
    }

    private function measure(AdminDashboardService $service, User $admin): int
    {
        DB::connection()->flushQueryLog();
        DB::connection()->enableQueryLog();
        $service->page($admin);
        $count = count(DB::connection()->getQueryLog());
        DB::connection()->disableQueryLog();

        return $count;
    }

    private function createRecords(int $count, string $suffix): void
    {
        for ($index = 1; $index <= $count; $index++) {
            $product = Product::create([
                'name' => "Query {$suffix} product {$index}",
                'slug' => "query-{$suffix}-product-{$index}-".uniqid(),
                'price' => 100000 + $index,
                'stock' => 5,
                'status' => 'active',
            ]);
            $order = Order::create([
                'order_number' => "ORD-QUERY-{$suffix}-{$index}",
                'customer_name' => 'Query customer',
                'total_amount' => 100000 + $index,
                'status' => 'pending',
            ]);
            Review::create([
                'product_id' => $product->id,
                'customer_name' => 'Query reviewer',
                'content' => 'Query review',
                'rating' => 5,
                'status' => 'pending',
            ]);
            Post::create([
                'title' => "Query {$suffix} post {$index}",
                'slug' => "query-{$suffix}-post-{$index}-".uniqid(),
                'content' => 'Query post',
                'status' => 'published',
            ]);
            Warranty::create([
                'order_id' => $order->id,
                'serial_number' => "QUERY-{$suffix}-{$index}-".uniqid(),
                'product_name' => $product->name,
                'status' => 'active',
            ]);
        }
    }
}
