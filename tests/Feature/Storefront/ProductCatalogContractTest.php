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

    public function test_product_index_validates_price_range(): void
    {
        $this->get('/san-pham?min_price=10&max_price=1')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Guest/Product/Index')
                ->where('page.catalog.filters.min_price', '10')
                ->where('page.catalog.filters.max_price', '1')
                ->where('errors.min_price.0', 'Giá tối thiểu phải nhỏ hơn hoặc bằng giá tối đa.')
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
}
