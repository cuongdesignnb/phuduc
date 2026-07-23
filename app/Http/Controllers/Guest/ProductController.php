<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Services\Storefront\ProductCatalogFilterResolver;
use App\Services\Storefront\ProductCatalogService;
use App\Services\Storefront\ProductDetailService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request, ProductCatalogFilterResolver $filters, ProductCatalogService $catalog)
    {
        $state = $filters->resolve($request);
        $page = $catalog->page($state['query_filters']);
        $page['page']['catalog']['filters'] = $state['display_filters'];

        if ($state['errors'] !== []) {
            $page['errors'] = $state['errors'];
        }

        return Inertia::render('Guest/Product/Index', $page);
    }

    public function show(string $slug, ProductDetailService $detail)
    {
        return Inertia::render('Guest/Product/Show', $detail->page($slug));
    }
}
