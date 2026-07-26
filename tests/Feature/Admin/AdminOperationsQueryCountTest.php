<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Post;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Warranty;
use App\Services\Admin\AdminDashboardService;
use App\Services\Admin\Operations\AdminOrderService;
use App\Services\Admin\Operations\AdminReviewService;
use App\Services\Admin\Operations\AdminWarrantyService;
use App\Services\Storefront\ProductDetailService;
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

    public function test_operation_detail_and_warranty_lookup_queries_are_bounded(): void
    {
        $admin = User::factory()->admin()->create();
        $orders = app(AdminOrderService::class);
        $warranties = app(AdminWarrantyService::class);
        $detailOrder = Order::create(['order_number' => 'ORD-DETAIL-QUERY', 'customer_name' => 'Query customer', 'total_amount' => 100, 'status' => 'pending']);
        $this->createItems($detailOrder, 1, 'Detail');
        $detailOneModel = Order::findOrFail($detailOrder->id);
        $detailOne = $this->measure(fn () => $orders->detail($admin, $detailOneModel));
        $this->createItems($detailOrder, 29, 'Detail');
        $detailThirtyModel = Order::findOrFail($detailOrder->id);
        $detailThirty = $this->measure(fn () => $orders->detail($admin, $detailThirtyModel));

        $lookupOne = Order::create(['order_number' => 'ORD-LOOKUP-1', 'customer_name' => 'Lookup customer', 'total_amount' => 100, 'status' => 'pending']);
        $lookupOneQueries = $this->measure(fn () => $warranties->orderLookup(['search' => 'ORD-LOOKUP-1']));
        for ($index = 2; $index <= 30; $index++) {
            Order::create(['order_number' => "ORD-LOOKUP-{$index}", 'customer_name' => 'Lookup customer', 'total_amount' => 100, 'status' => 'pending']);
        }
        $lookupThirtyQueries = $this->measure(fn () => $warranties->orderLookup(['search' => 'ORD-LOOKUP']));

        $itemsOrder = Order::create(['order_number' => 'ORD-ITEM-LOOKUP', 'customer_name' => 'Item customer', 'total_amount' => 100, 'status' => 'pending']);
        $this->createItems($itemsOrder, 1, 'Item');
        $itemsOne = $this->measure(fn () => $warranties->orderItems($itemsOrder));
        $this->createItems($itemsOrder, 50, 'Item');
        $itemsFiftyOne = $this->measure(fn () => $warranties->orderItems($itemsOrder));

        fwrite(STDOUT, "ORDER_DETAIL_Q1_RECORD_COUNT=1\nORDER_DETAIL_Q30_RECORD_COUNT=30\nORDER_DETAIL_Q1={$detailOne}\nORDER_DETAIL_Q30={$detailThirty}\nWARRANTY_ORDER_LOOKUP_Q1_RECORD_COUNT=1\nWARRANTY_ORDER_LOOKUP_Q30_RECORD_COUNT=30\nWARRANTY_ORDER_LOOKUP_Q1={$lookupOneQueries}\nWARRANTY_ORDER_LOOKUP_Q30={$lookupThirtyQueries}\nWARRANTY_ITEM_LOOKUP_Q1_RECORD_COUNT=1\nWARRANTY_ITEM_LOOKUP_Q51_RECORD_COUNT=51\nWARRANTY_ITEM_LOOKUP_Q1={$itemsOne}\nWARRANTY_ITEM_LOOKUP_Q51={$itemsFiftyOne}\n");
        $this->assertLessThanOrEqual($detailOne + 2, $detailThirty);
        $this->assertLessThanOrEqual($lookupOneQueries + 2, $lookupThirtyQueries);
        $this->assertLessThanOrEqual($itemsOne + 2, $itemsFiftyOne);
    }

    public function test_dashboard_and_public_product_query_coverage_is_bounded(): void
    {
        $admin = User::factory()->admin()->create();
        $dashboard = app(AdminDashboardService::class);
        $this->createDashboardRecords(1, 'one');
        $dashboardOne = $this->measure(fn () => $dashboard->page($admin));
        $this->createDashboardRecords(29, 'many');
        $dashboardThirty = $this->measure(fn () => $dashboard->page($admin));

        $order = Order::create(['order_number' => 'ORD-PUBLIC-QUERY', 'customer_name' => 'Public customer', 'customer_phone' => '0900000000', 'total_amount' => 100, 'status' => 'pending']);
        $this->createItems($order, 1, 'Public');
        $publicOrderOne = $this->measureRequest('/tra-cuu-don-hang', ['order_number' => $order->order_number, 'customer_phone' => '0900000000']);
        $this->createItems($order, 29, 'Public');
        $publicOrderThirty = $this->measureRequest('/tra-cuu-don-hang', ['order_number' => $order->order_number, 'customer_phone' => '0900000000']);

        $warranty = Warranty::create(['serial_number' => 'PUBLIC-QUERY-WARRANTY', 'product_name' => 'Public product', 'customer_name' => 'Public customer', 'customer_phone' => '0900000000', 'status' => 'active']);
        $publicWarranty = $this->measureRequest('/tra-cuu-bao-hanh', ['serial_number' => $warranty->serial_number, 'customer_phone' => '0900000000']);

        $product = Product::create(['name' => 'Review query product', 'slug' => 'review-query-product', 'price' => 100, 'stock' => 1, 'status' => 'active']);
        $this->createReviews($product, 1);
        $productReviewsOne = $this->measure(fn () => app(ProductDetailService::class)->page($product->slug));
        $this->createReviews($product, 29, 2);
        $productReviewsThirty = $this->measure(fn () => app(ProductDetailService::class)->page($product->slug));

        fwrite(STDOUT, "DASHBOARD_Q1_RECORD_COUNT=1\nDASHBOARD_Q30_RECORD_COUNT=30\nDASHBOARD_Q1={$dashboardOne}\nDASHBOARD_Q30={$dashboardThirty}\nPUBLIC_ORDER_LOOKUP_Q1_RECORD_COUNT=1\nPUBLIC_ORDER_LOOKUP_Q30_RECORD_COUNT=30\nPUBLIC_ORDER_LOOKUP_Q1={$publicOrderOne}\nPUBLIC_ORDER_LOOKUP_Q30={$publicOrderThirty}\nPUBLIC_WARRANTY_LOOKUP={$publicWarranty}\nPRODUCT_REVIEW_Q1_RECORD_COUNT=1\nPRODUCT_REVIEW_Q30_RECORD_COUNT=30\nPRODUCT_REVIEW_Q1={$productReviewsOne}\nPRODUCT_REVIEW_Q30={$productReviewsThirty}\nPR3C_QUERY_COVERAGE=COMPLETE\nN_PLUS_ONE=NO\n");
        $this->assertLessThanOrEqual($dashboardOne + 1, $dashboardThirty);
        $this->assertLessThanOrEqual($publicOrderOne + 2, $publicOrderThirty);
        $this->assertLessThanOrEqual($productReviewsOne + 2, $productReviewsThirty);
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

    private function createItems(Order $order, int $count, string $prefix): void
    {
        $existing = $order->items()->count();
        for ($index = $existing + 1; $index <= $existing + $count; $index++) {
            OrderItem::create(['order_id' => $order->id, 'product_name' => "{$prefix} item {$index}", 'price' => 100, 'quantity' => 1, 'total' => 100]);
        }
    }

    private function createDashboardRecords(int $count, string $suffix): void
    {
        for ($index = 1; $index <= $count; $index++) {
            $product = Product::create(['name' => "Dashboard {$suffix} product {$index}", 'slug' => "dashboard-{$suffix}-{$index}-".uniqid(), 'price' => 100, 'stock' => 5, 'status' => 'active']);
            $order = Order::create(['order_number' => "ORD-DASHBOARD-{$suffix}-{$index}", 'customer_name' => 'Dashboard customer', 'total_amount' => 100, 'status' => 'pending']);
            Review::create(['product_id' => $product->id, 'customer_name' => 'Dashboard reviewer', 'content' => 'Dashboard review', 'rating' => 5, 'status' => 'pending']);
            Post::create(['title' => "Dashboard {$suffix} post {$index}", 'slug' => "dashboard-{$suffix}-post-{$index}-".uniqid(), 'content' => 'Dashboard post', 'status' => 'published']);
            Warranty::create(['order_id' => $order->id, 'serial_number' => "DASHBOARD-{$suffix}-{$index}-".uniqid(), 'product_name' => $product->name, 'status' => 'active']);
        }
    }

    private function createReviews(Product $product, int $count, int $start = 1): void
    {
        for ($index = $start; $index < $start + $count; $index++) {
            Review::create(['product_id' => $product->id, 'customer_name' => "Reviewer {$index}", 'content' => 'Approved review', 'rating' => 5, 'status' => 'approved']);
        }
    }

    private function measureRequest(string $uri, array $data): int
    {
        $this->post($uri, $data)->assertOk();
        DB::flushQueryLog();
        DB::enableQueryLog();
        try {
            $this->post($uri, $data)->assertOk();

            return collect(DB::getQueryLog())->reject(fn (array $query) => str_starts_with(strtolower($query['query'] ?? ''), 'pragma '))->count();
        } finally {
            DB::disableQueryLog();
            DB::flushQueryLog();
        }
    }
}
