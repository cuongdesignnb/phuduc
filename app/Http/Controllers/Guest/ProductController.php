<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::where('status', 'active')
            ->with(['images' => fn($q) => $q->where('is_360', false)->orderBy('sort_order')->limit(1)])
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->min_price, fn($q, $p) => $q->where('price', '>=', $p))
            ->when($request->max_price, fn($q, $p) => $q->where('price', '<=', $p))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Guest/Product/Index', [
            'products' => $products,
            'filters' => $request->only('search', 'min_price', 'max_price'),
            'seo' => SeoService::meta([
                'title' => 'Sản phẩm',
                'description' => 'Danh sách sản phẩm công nghiệp chất lượng cao',
            ]),
            'jsonLd' => SeoService::breadcrumbJsonLd([
                ['name' => 'Trang chủ', 'url' => url('/')],
                ['name' => 'Sản phẩm', 'url' => url('/san-pham')],
            ]),
        ]);
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('status', 'active')
            ->with(['images', 'approvedReviews' => fn($q) => $q->latest()->limit(20)])
            ->firstOrFail();

        $relatedProducts = Product::where('status', 'active')
            ->where('id', '!=', $product->id)
            ->with(['images' => fn($q) => $q->where('is_360', false)->orderBy('sort_order')->limit(1)])
            ->inRandomOrder()
            ->limit(4)
            ->get();

        $firstImage = $product->images->where('is_360', false)->sortBy('sort_order')->first();

        return Inertia::render('Guest/Product/Show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'seo' => SeoService::meta([
                'title' => $product->name,
                'description' => mb_substr(strip_tags($product->description ?? ''), 0, 160),
                'ogImage' => $firstImage ? url("storage/{$firstImage->image_path}") : '',
                'ogType' => 'product',
            ]),
            'jsonLd' => [
                SeoService::productJsonLd($product),
                SeoService::breadcrumbJsonLd([
                    ['name' => 'Trang chủ', 'url' => url('/')],
                    ['name' => 'Sản phẩm', 'url' => url('/san-pham')],
                    ['name' => $product->name],
                ]),
            ],
        ]);
    }
}
