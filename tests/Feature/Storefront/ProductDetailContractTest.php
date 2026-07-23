<?php

namespace Tests\Feature\Storefront;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductDetailContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_detail_returns_public_canonical_contract(): void
    {
        $product = Product::create([
            'name' => 'Main Product',
            'slug' => 'main-product',
            'sku' => 'PD-1',
            'price' => 0,
            'stock' => 0,
            'description' => '<p>Safe</p><script>alert(1)</script>',
            'status' => 'active',
            'specifications' => [['key' => 'payload', 'value' => '500 kg']],
        ]);
        ProductImage::create(['product_id' => $product->id, 'image_path' => 'products/main.webp', 'is_360' => false]);
        ProductImage::create(['product_id' => $product->id, 'image_path' => 'products/spin.webp', 'is_360' => true]);
        Review::create([
            'product_id' => $product->id,
            'customer_name' => 'Customer',
            'customer_email' => 'private@example.com',
            'customer_phone' => '0123',
            'content' => 'Nice',
            'rating' => 4,
            'status' => 'approved',
        ]);
        Product::create(['name' => 'Related', 'slug' => 'related', 'status' => 'active', 'created_at' => now()->addMinute()]);
        Product::create(['name' => 'Inactive', 'slug' => 'inactive', 'status' => 'inactive']);

        $this->get('/san-pham/main-product')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Guest/Product/Show')
                ->where('page.type', 'product_detail')
                ->where('page.product.price_display', 'Lien he')
                ->where('page.product.gallery.0.url', url('/storage/products/main.webp'))
                ->where('page.product.spin_frames.0.url', url('/storage/products/spin.webp'))
                ->where('page.product.review_summary.count', 1)
                ->where('page.product.review_summary.average_rating', 4)
                ->where('page.product.reviews.0.customer_name', 'Customer')
                ->missing('page.product.reviews.0.customer_email')
                ->missing('page.product.reviews.0.customer_phone')
                ->where('page.related_products.0.slug', 'related')
                ->missing('product')
            );
    }

    public function test_inactive_product_returns_404(): void
    {
        Product::create(['name' => 'Inactive', 'slug' => 'inactive', 'status' => 'inactive']);

        $this->get('/san-pham/inactive')->assertNotFound();
    }
}
