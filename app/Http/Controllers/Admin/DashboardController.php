<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Post;
use App\Models\Warranty;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $totalOrders = Order::count();
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_amount');
        $totalProducts = Product::count();
        $totalReviews = Review::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $pendingReviews = Review::where('status', 'pending')->count();
        $activeProducts = Product::where('status', 'active')->count();
        $totalPosts = Post::where('status', 'published')->count();
        $activeWarranties = Warranty::where('status', 'active')->count();

        $ordersByStatus = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $monthlyRevenue = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $recentOrders = Order::latest()->take(5)->get();
        $recentReviews = Review::with('product:id,name')->latest()->take(5)->get();
        $topProducts = Product::withCount('reviews')
            ->orderByDesc('reviews_count')
            ->take(5)
            ->get(['id', 'name', 'price', 'stock', 'status']);

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalOrders' => $totalOrders,
                'totalRevenue' => $totalRevenue,
                'totalProducts' => $totalProducts,
                'totalReviews' => $totalReviews,
                'pendingOrders' => $pendingOrders,
                'pendingReviews' => $pendingReviews,
                'activeProducts' => $activeProducts,
                'totalPosts' => $totalPosts,
                'activeWarranties' => $activeWarranties,
            ],
            'ordersByStatus' => $ordersByStatus,
            'monthlyRevenue' => $monthlyRevenue,
            'recentOrders' => $recentOrders,
            'recentReviews' => $recentReviews,
            'topProducts' => $topProducts,
        ]);
    }
}
