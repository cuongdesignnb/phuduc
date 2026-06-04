<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use App\Models\Post;
use App\Models\Product;
use App\Models\Setting;
use App\Services\SeoService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class PageController extends Controller
{
    public function home()
    {
        $featuredProductsLimit = max(1, min(12, (int) Setting::get('home.featured_products_limit', 4)));
        $latestPostsLimit = max(1, min(12, (int) Setting::get('home.latest_posts_limit', 3)));

        $featuredProducts = Product::where('status', 'active')
            ->with(['images' => fn($query) => $query->where('is_360', false)->orderBy('sort_order')->limit(1)])
            ->latest()
            ->limit($featuredProductsLimit)
            ->get();

        $latestPosts = Post::where('status', 'published')
            ->with('category:id,name')
            ->latest()
            ->limit($latestPostsLimit)
            ->get();

        $settings = Setting::where('key', 'like', 'site.%')
            ->orWhere('key', 'like', 'home.%')
            ->pluck('value', 'key');

        $homeSections = HomeSection::where('is_enabled', true)
            ->with(['activeItems' => fn($query) => $query->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->keyBy('key')
            ->map(fn(HomeSection $section) => [
                'key' => $section->key,
                'title' => $section->title,
                'subtitle' => $section->subtitle,
                'description' => $section->description,
                'settings_json' => $section->settings_json,
                'items' => $section->activeItems->values(),
            ]);

        return Inertia::render('Guest/Home', [
            'featuredProducts' => $featuredProducts,
            'latestPosts' => $latestPosts,
            'settings' => $settings,
            'homeSections' => $homeSections,
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'seo' => SeoService::meta([
                'description' => Setting::get('site.description', 'Giải pháp công nghiệp hàng đầu'),
            ]),
            'jsonLd' => SeoService::organizationJsonLd(),
        ]);
    }

    public function about()
    {
        $settings = Setting::where('key', 'like', 'about.%')
            ->orWhere('key', 'like', 'site.%')
            ->pluck('value', 'key');

        return Inertia::render('Guest/About', [
            'settings' => $settings,
            'seo' => SeoService::meta([
                'title' => 'Giới thiệu',
                'description' => Setting::get('about.description', 'Giới thiệu về công ty'),
            ]),
            'jsonLd' => [
                SeoService::organizationJsonLd(),
                SeoService::breadcrumbJsonLd([
                    ['name' => 'Trang chủ', 'url' => url('/')],
                    ['name' => 'Giới thiệu'],
                ]),
            ],
        ]);
    }
}
