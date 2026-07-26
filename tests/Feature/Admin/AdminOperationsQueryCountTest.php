<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Warranty;
use App\Services\Admin\Operations\AdminOrderService;
use App\Services\Admin\Operations\AdminReviewService;
use App\Services\Admin\Operations\AdminWarrantyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminOperationsQueryCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_operation_indexes_are_bounded_when_rows_scale(): void
    {
        $admin = User::factory()->admin()->create();
        $orders = app(AdminOrderService::class);
        $reviews = app(AdminReviewService::class);
        $warranties = app(AdminWarrantyService::class);

        $this->createRecords(1);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('reviews', 1);
        $this->assertDatabaseCount('warranties', 1);
        $orderOne = $this->measure(fn () => $orders->index($admin, []));
        $reviewOne = $this->measure(fn () => $reviews->index($admin, []));
        $warrantyOne = $this->measure(fn () => $warranties->index($admin, []));
        $this->createRecords(29, 2);
        $this->assertDatabaseCount('orders', 30);
        $this->assertDatabaseCount('reviews', 30);
        $this->assertDatabaseCount('warranties', 30);
        $orderThirty = $this->measure(fn () => $orders->index($admin, []));
        $reviewThirty = $this->measure(fn () => $reviews->index($admin, []));
        $warrantyThirty = $this->measure(fn () => $warranties->index($admin, []));

        fwrite(STDOUT, "Q1_RECORD_COUNT=1\nQ30_RECORD_COUNT=30\nORDER_INDEX_Q1={$orderOne}\nORDER_INDEX_Q30={$orderThirty}\nREVIEW_INDEX_Q1={$reviewOne}\nREVIEW_INDEX_Q30={$reviewThirty}\nWARRANTY_INDEX_Q1={$warrantyOne}\nWARRANTY_INDEX_Q30={$warrantyThirty}\n");
        $this->assertLessThanOrEqual($orderOne + 2, $orderThirty);
        $this->assertLessThanOrEqual($reviewOne + 2, $reviewThirty);
        $this->assertLessThanOrEqual($warrantyOne + 2, $warrantyThirty);
    }

    private function measure(callable $callback): int
    {
        DB::connection()->flushQueryLog();
        DB::connection()->enableQueryLog();
        $callback();
        $count = count(DB::connection()->getQueryLog());
        DB::connection()->disableQueryLog();

        return $count;
    }

    private function createRecords(int $count, int $start = 1): void
    {
        for ($index = $start; $index < $start + $count; $index++) {
            $product = Product::create(['name' => "Query product {$index}", 'slug' => "query-product-{$index}", 'price' => 100000, 'stock' => 5, 'status' => 'active']);
            $order = Order::create(['order_number' => "ORD-QUERY-{$index}", 'customer_name' => 'Query customer', 'total_amount' => 100000, 'status' => 'pending']);
            Review::create(['product_id' => $product->id, 'customer_name' => 'Query reviewer', 'content' => 'Query review', 'rating' => 5, 'status' => 'pending']);
            Warranty::create(['order_id' => $order->id, 'serial_number' => "QUERY-{$index}", 'product_name' => $product->name, 'status' => 'active']);
        }
    }
}
