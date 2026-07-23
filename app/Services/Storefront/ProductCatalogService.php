<?php

namespace App\Services\Storefront;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductCatalogService
{
    private const PER_PAGE = 12;

    public function __construct(
        private readonly ProductPresentationService $products,
        private readonly StorefrontSeoService $seo,
    ) {}

    /**
     * @param  array{search: ?string, min_price: mixed, max_price: mixed, sort: string}  $filters
     * @return array<string, mixed>
     */
    public function page(array $filters): array
    {
        $query = Product::query()
            ->select(['id', 'name', 'slug', 'price', 'sku', 'specifications', 'status', 'created_at'])
            ->where('status', 'active')
            ->with('cardImage')
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->when($filters['search'], fn (Builder $query, string $search) => $query->where(function (Builder $query) use ($search): void {
                $keyword = $this->escapeLike($search);
                $query->whereRaw("name LIKE ? ESCAPE '\\'", ["%{$keyword}%"])
                    ->orWhereRaw("sku LIKE ? ESCAPE '\\'", ["%{$keyword}%"]);
            }))
            ->when($filters['min_price'] !== null, fn (Builder $query) => $query->where('price', '>=', $filters['min_price']))
            ->when($filters['max_price'] !== null, fn (Builder $query) => $query->where('price', '<=', $filters['max_price']));

        $this->applySort($query, $filters['sort']);

        $paginator = $query
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $breadcrumbs = [
            ['name' => 'Trang chu', 'url' => url('/')],
            ['name' => 'San pham', 'url' => route('products.index')],
        ];
        $hasFilters = filled($filters['search']) || $filters['min_price'] !== null || $filters['max_price'] !== null;

        return [
            'page' => [
                'type' => 'product_index',
                'seo' => $this->seo->meta([
                    'title' => 'San pham',
                    'description' => 'Danh sach san pham xe dien cong nghiep',
                    'canonical' => route('products.index'),
                    'robots' => $hasFilters ? 'noindex, follow' : 'index, follow',
                ]),
                'json_ld' => [$this->seo->breadcrumbJsonLd($breadcrumbs)],
                'breadcrumbs' => $breadcrumbs,
                'hero' => [
                    'eyebrow' => 'San pham',
                    'title' => 'Giai phap xe dien cong nghiep',
                    'description' => 'Kham pha cac dong xe dien phu hop cho van chuyen va van hanh noi bo.',
                ],
                'catalog' => [
                    'items' => $paginator->getCollection()->map(fn (Product $product) => $this->products->present($product))->values()->all(),
                    'pagination' => $this->pagination($paginator),
                    'filters' => [
                        'search' => $filters['search'] ?? '',
                        'min_price' => $filters['min_price'],
                        'max_price' => $filters['max_price'],
                        'sort' => $filters['sort'],
                    ],
                    'sort_options' => [
                        ['value' => 'latest', 'label' => 'Moi nhat'],
                        ['value' => 'price_asc', 'label' => 'Gia tang dan'],
                        ['value' => 'price_desc', 'label' => 'Gia giam dan'],
                        ['value' => 'name_asc', 'label' => 'Ten A-Z'],
                        ['value' => 'name_desc', 'label' => 'Ten Z-A'],
                    ],
                ],
            ],
        ];
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'price_asc' => $query->orderBy('price')->orderBy('id'),
            'price_desc' => $query->orderByDesc('price')->orderByDesc('id'),
            'name_asc' => $query->orderBy('name')->orderBy('id'),
            'name_desc' => $query->orderByDesc('name')->orderByDesc('id'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };
    }

    private function escapeLike(string $value): string
    {
        return addcslashes($value, '\%_');
    }

    /**
     * @return array<string, mixed>
     */
    private function pagination(LengthAwarePaginator $paginator): array
    {
        return [
            'links' => $paginator->linkCollection()->toArray(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }
}
