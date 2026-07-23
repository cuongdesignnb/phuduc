<?php

namespace Tests\Feature\Storefront;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductCatalogContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_index_returns_canonical_contract_with_cards(): void
    {
        $product = Product::create([
            'name' => 'Lift Truck',
            'slug' => 'lift-truck',
            'sku' => 'PD_100',
            'price' => 1000000,
            'status' => 'active',
            'specifications' => [['key' => 'payload', 'value' => '500 kg']],
        ]);
        ProductImage::create(['product_id' => $product->id, 'image_path' => 'products/card.webp']);
        Review::create(['product_id' => $product->id, 'customer_name' => 'A', 'content' => 'Good', 'rating' => 5, 'status' => 'approved']);
        Product::create(['name' => 'Inactive', 'slug' => 'inactive', 'status' => 'inactive']);

        $this->get('/san-pham?search=PD_100')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Guest/Product/Index')
                ->where('page.type', 'product_index')
                ->has('page.catalog.items', 1)
                ->where('page.catalog.items.0.id', $product->id)
                ->where('page.catalog.items.0.image_url', url('/storage/products/card.webp'))
                ->where('page.catalog.items.0.review_count', 1)
                ->where('page.catalog.items.0.average_rating', 5)
                ->where('page.catalog.filters.search', 'PD_100')
                ->where('page.seo.robots', 'noindex, follow')
                ->missing('products')
            );
    }

    public function test_product_sort_is_deterministic(): void
    {
        Product::create(['name' => 'B', 'slug' => 'b', 'price' => 100, 'status' => 'active']);
        Product::create(['name' => 'A', 'slug' => 'a', 'price' => 100, 'status' => 'active']);

        $this->get('/san-pham?sort=name_asc')
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.catalog.items.0.slug', 'a')
                ->where('page.catalog.items.1.slug', 'b')
            );
    }

    public function test_long_search_is_rejected_preserved_and_excluded_from_query_filters(): void
    {
        Product::create(['name' => 'Visible Product', 'slug' => 'visible-product', 'price' => 100, 'status' => 'active']);
        $search = str_repeat('x', 101);

        $this->get('/san-pham?search='.$search)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('page.catalog.items', 1)
                ->where('page.catalog.filters.search', $search)
                ->has('errors.search.0')
            );
    }

    public function test_negative_min_price_is_rejected_preserved_and_excluded(): void
    {
        Product::create(['name' => 'Visible Product', 'slug' => 'visible-product', 'price' => 100, 'status' => 'active']);

        $this->get('/san-pham?min_price=-1')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('page.catalog.items', 1)
                ->where('page.catalog.filters.min_price', '-1')
                ->has('errors.min_price.0')
            );
    }

    public function test_negative_max_price_is_rejected_preserved_and_excluded(): void
    {
        Product::create(['name' => 'Visible Product', 'slug' => 'visible-product', 'price' => 100, 'status' => 'active']);

        $this->get('/san-pham?max_price=-1')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('page.catalog.items', 1)
                ->where('page.catalog.filters.max_price', '-1')
                ->has('errors.max_price.0')
            );
    }

    public function test_price_above_maximum_is_rejected_preserved_and_excluded(): void
    {
        Product::create(['name' => 'Visible Product', 'slug' => 'visible-product', 'price' => 100, 'status' => 'active']);

        $this->get('/san-pham?max_price=1000000000001')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('page.catalog.items', 1)
                ->where('page.catalog.filters.max_price', '1000000000001')
                ->has('errors.max_price.0')
            );
    }

    public function test_invalid_sort_falls_back_to_latest_query_sort_and_preserves_raw_value(): void
    {
        Product::create(['name' => 'Older', 'slug' => 'older', 'price' => 100, 'status' => 'active', 'created_at' => now()->subDay()]);
        Product::create(['name' => 'Newer', 'slug' => 'newer', 'price' => 100, 'status' => 'active', 'created_at' => now()]);

        $this->get('/san-pham?sort=invalid')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.catalog.items.0.slug', 'newer')
                ->where('page.catalog.filters.sort', 'invalid')
                ->has('errors.sort.0')
            );
    }

    public function test_invalid_range_excludes_both_price_filters_and_preserves_raw_values(): void
    {
        Product::create(['name' => 'Cheap', 'slug' => 'cheap', 'price' => 50, 'status' => 'active']);
        Product::create(['name' => 'Expensive', 'slug' => 'expensive', 'price' => 2000, 'status' => 'active']);

        $this->get('/san-pham?min_price=1000&max_price=100')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('page.catalog.items', 2)
                ->where('page.catalog.filters.min_price', '1000')
                ->where('page.catalog.filters.max_price', '100')
                ->where('errors.min_price.0', 'Giá tối thiểu phải nhỏ hơn hoặc bằng giá tối đa.')
            );
    }

    public function test_product_filter_validation_has_single_architectural_source(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Guest/ProductController.php'));
        $resolver = file_get_contents(app_path('Services/Storefront/ProductCatalogFilterResolver.php'));

        $this->assertFileDoesNotExist(app_path('Http/Requests/Storefront/ProductCatalogRequest.php'));
        $this->assertStringNotContainsString('Validator::make', $controller);
        $this->assertStringNotContainsString('filtersFromQuery', $controller);
        $this->assertStringNotContainsString('querySafeFilters', $controller);
        $this->assertSame(1, substr_count($resolver, "'min_price' => ['nullable', 'numeric'"));
        $this->assertSame(1, substr_count($resolver, "'sort' => ['nullable', 'in:'"));
    }
}
