<?php

namespace Tests\Feature\Storefront;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StorefrontSeoContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_schema_uses_full_review_aggregate(): void
    {
        $product = Product::create(['name' => 'Schema Product', 'slug' => 'schema-product', 'price' => 1000, 'stock' => 1, 'status' => 'active']);
        Review::create(['product_id' => $product->id, 'customer_name' => 'A', 'content' => 'A', 'rating' => 5, 'status' => 'approved']);
        Review::create(['product_id' => $product->id, 'customer_name' => 'B', 'content' => 'B', 'rating' => 3, 'status' => 'approved']);

        $this->get('/san-pham/schema-product')
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.json_ld.0.aggregateRating.reviewCount', 2)
                ->where('page.json_ld.0.aggregateRating.ratingValue', 4)
                ->where('page.json_ld.0.offers.priceCurrency', 'VND')
            );
    }
}
