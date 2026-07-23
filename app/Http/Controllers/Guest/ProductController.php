<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Services\Storefront\ProductCatalogService;
use App\Services\Storefront\ProductDetailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request, ProductCatalogService $catalog)
    {
        $validator = Validator::make($request->query(), [
            'search' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'min:0', 'max:1000000000000'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'max:1000000000000'],
            'sort' => ['nullable', 'in:latest,price_asc,price_desc,name_asc,name_desc'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $validator->after(function ($validator) use ($request): void {
            $min = $request->query('min_price');
            $max = $request->query('max_price');

            if ($min !== null && $max !== null && is_numeric($min) && is_numeric($max) && (float) $min > (float) $max) {
                $validator->errors()->add('min_price', 'Giá tối thiểu phải nhỏ hơn hoặc bằng giá tối đa.');
            }
        });

        $filters = $this->filtersFromQuery($request);
        $page = $catalog->page($validator->fails() ? $this->querySafeFilters($filters) : $filters);

        if ($validator->fails()) {
            $page['page']['catalog']['filters'] = $filters;
            $page['errors'] = $validator->errors()->toArray();
        }

        return Inertia::render('Guest/Product/Index', $page);
    }

    public function show(string $slug, ProductDetailService $detail)
    {
        return Inertia::render('Guest/Product/Show', $detail->page($slug));
    }

    /**
     * @return array{search: ?string, min_price: mixed, max_price: mixed, sort: string}
     */
    private function filtersFromQuery(Request $request): array
    {
        $sort = $request->query('sort');

        return [
            'search' => filled($request->query('search')) ? trim((string) $request->query('search')) : null,
            'min_price' => $request->query('min_price') !== null ? $request->query('min_price') : null,
            'max_price' => $request->query('max_price') !== null ? $request->query('max_price') : null,
            'sort' => in_array($sort, ['latest', 'price_asc', 'price_desc', 'name_asc', 'name_desc'], true) ? $sort : 'latest',
        ];
    }

    /**
     * @param  array{search: ?string, min_price: mixed, max_price: mixed, sort: string}  $filters
     * @return array{search: ?string, min_price: ?float, max_price: ?float, sort: string}
     */
    private function querySafeFilters(array $filters): array
    {
        return [
            'search' => $filters['search'],
            'min_price' => is_numeric($filters['min_price']) ? (float) $filters['min_price'] : null,
            'max_price' => is_numeric($filters['max_price']) ? (float) $filters['max_price'] : null,
            'sort' => $filters['sort'],
        ];
    }
}
