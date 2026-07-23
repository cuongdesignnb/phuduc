<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\ProductCatalogRequest;
use App\Services\Storefront\ProductCatalogService;
use App\Services\Storefront\ProductDetailService;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(ProductCatalogRequest $request, ProductCatalogService $catalog)
    {
        return Inertia::render('Guest/Product/Index', $catalog->page($request->filters()));
    }

    public function show(string $slug, ProductDetailService $detail)
    {
        return Inertia::render('Guest/Product/Show', $detail->page($slug));
    }
}
