<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Warranty;
use Carbon\CarbonImmutable;

class AdminDashboardService
{
    public function __construct(
        private readonly AdminNavigationService $navigation,
        private readonly AdminPermissionService $permissions,
        private readonly AdminPresentationService $presentation,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function page(?User $user = null): array
    {
        $now = CarbonImmutable::now();
        $monthStart = $now->startOfMonth()->subMonths(5);

        $orderMetrics = Order::query()
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw('COALESCE(SUM(CASE WHEN status != ? THEN total_amount ELSE 0 END), 0) as total_revenue', ['cancelled'])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as pending_orders', ['pending'])
            ->first();
        $productMetrics = Product::query()
            ->selectRaw('COUNT(*) as total_products')
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as active_products', ['active'])
            ->first();
        $reviewMetrics = Review::query()
            ->selectRaw('COUNT(*) as total_reviews')
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as pending_reviews', ['pending'])
            ->first();
        $publishedPosts = Post::query()->where('status', 'published')->count();
        $activeWarranties = Warranty::query()->where('status', 'active')->count();

        $ordersByStatus = Order::query()
            ->select('status')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->map(fn (Order $order): array => [
                'key' => $order->status,
                'label' => $this->presentation->statusLabel($order->status),
                'count' => (int) $order->count,
            ])->values()->all();

        $revenueRows = Order::query()
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $monthStart)
            ->get(['created_at', 'total_amount']);

        $monthlyRevenue = collect(range(0, 5))->map(function (int $offset) use ($monthStart, $revenueRows): array {
            $month = $monthStart->addMonths($offset);
            $rows = $revenueRows->filter(fn (Order $order): bool => $order->created_at?->format('Y-m') === $month->format('Y-m'));
            $money = $this->presentation->money($rows->sum(fn (Order $order): float => (float) $order->total_amount));

            return [
                'month' => $month->format('Y-m'),
                'label' => $month->format('m/Y'),
                'revenue' => $money['value'],
                'revenue_display' => $money['display'],
                'orders' => $rows->count(),
            ];
        })->all();
        $maxRevenue = max(1, ...array_column($monthlyRevenue, 'revenue'));
        $monthlyRevenue = array_map(fn (array $month): array => [...$month, 'percentage' => round(($month['revenue'] / $maxRevenue) * 100)], $monthlyRevenue);

        $recentOrders = Order::query()
            ->select(['id', 'order_number', 'status', 'customer_name', 'total_amount', 'created_at'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Order $order): array => $this->order($order))
            ->all();
        $recentReviews = Review::query()
            ->select(['id', 'product_id', 'customer_name', 'rating', 'status', 'created_at'])
            ->with('product:id,name')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Review $review): array => [
                'id' => $review->id,
                'reviewer_name' => $review->customer_name,
                'product_name' => $review->product?->name,
                'rating' => (int) $review->rating,
                'status' => $this->presentation->status($review->status),
                'status_label' => $this->presentation->statusLabel($review->status),
                'created_at_display' => $this->presentation->date($review->created_at),
                'admin_url' => route('admin.reviews.index'),
            ])->all();
        $topProducts = Product::query()
            ->select(['id', 'name', 'price', 'stock', 'status'])
            ->withCount('reviews')
            ->orderByDesc('reviews_count')
            ->limit(5)
            ->get()
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $this->presentation->money($product->price),
                'stock' => (int) $product->stock,
                'status' => $this->presentation->status($product->status),
                'status_label' => $this->presentation->statusLabel($product->status),
                'review_count' => (int) $product->reviews_count,
                'admin_url' => route('admin.products.edit', $product),
            ])->all();

        return [
            'page' => [
                'type' => 'admin_dashboard',
                'meta' => ['title' => 'Tổng quan'],
                'admin' => [
                    'navigation' => $this->navigation->for($user),
                    'breadcrumbs' => [['label' => 'Tổng quan', 'url' => route('dashboard')]],
                    'permissions' => $this->permissions->for($user),
                ],
                'dashboard' => [
                    'summary' => $this->summary($orderMetrics, $productMetrics, $reviewMetrics, $publishedPosts, $activeWarranties),
                    'orders_by_status' => $ordersByStatus,
                    'monthly_revenue' => $monthlyRevenue,
                    'recent_orders' => $recentOrders,
                    'recent_reviews' => $recentReviews,
                    'top_products' => $topProducts,
                ],
            ],
        ];
    }

    private function summary(mixed $orders, mixed $products, mixed $reviews, int $publishedPosts, int $activeWarranties): array
    {
        return [
            ['key' => 'total_orders', 'label' => 'Tổng đơn hàng', 'value' => (int) ($orders->total_orders ?? 0), 'display' => (string) ($orders->total_orders ?? 0), 'trend' => null],
            ['key' => 'revenue', 'label' => 'Doanh thu hợp lệ', 'value' => $this->presentation->money($orders->total_revenue ?? 0)['value'], 'display' => $this->presentation->money($orders->total_revenue ?? 0)['display'], 'trend' => null],
            ['key' => 'total_products', 'label' => 'Tổng sản phẩm', 'value' => (int) ($products->total_products ?? 0), 'display' => (string) ($products->total_products ?? 0), 'trend' => null],
            ['key' => 'active_products', 'label' => 'Sản phẩm đang bán', 'value' => (int) ($products->active_products ?? 0), 'display' => (string) ($products->active_products ?? 0), 'trend' => null],
            ['key' => 'pending_orders', 'label' => 'Đơn chờ xử lý', 'value' => (int) ($orders->pending_orders ?? 0), 'display' => (string) ($orders->pending_orders ?? 0), 'trend' => null],
            ['key' => 'total_reviews', 'label' => 'Tổng đánh giá', 'value' => (int) ($reviews->total_reviews ?? 0), 'display' => (string) ($reviews->total_reviews ?? 0), 'trend' => null],
            ['key' => 'pending_reviews', 'label' => 'Đánh giá chờ duyệt', 'value' => (int) ($reviews->pending_reviews ?? 0), 'display' => (string) ($reviews->pending_reviews ?? 0), 'trend' => null],
            ['key' => 'published_posts', 'label' => 'Bài viết đã đăng', 'value' => $publishedPosts, 'display' => (string) $publishedPosts, 'trend' => null],
            ['key' => 'active_warranties', 'label' => 'Bảo hành đang hoạt động', 'value' => $activeWarranties, 'display' => (string) $activeWarranties, 'trend' => null],
        ];
    }

    private function order(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $order->customer_name,
            'status' => $this->presentation->status($order->status),
            'status_label' => $this->presentation->statusLabel($order->status),
            'total' => $this->presentation->money($order->total_amount),
            'created_at_display' => $this->presentation->date($order->created_at),
            'admin_url' => route('admin.orders.show', $order),
        ];
    }
}
