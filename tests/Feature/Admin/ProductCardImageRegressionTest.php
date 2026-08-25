<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductCardImageRegressionTest extends TestCase
{
    use Pr3bTestHelpers, RefreshDatabase;

    public function test_admin_products_index_resolves_card_images_with_mysql_safe_projection(): void
    {
        $admin = $this->admin();
        $older = now()->subMinutes(2);
        $newer = now()->subMinute();
        $first = Product::create([
            'name' => 'First product',
            'slug' => 'first-product',
            'price' => 100000,
            'stock' => 1,
            'status' => 'active',
            'created_at' => $older,
            'updated_at' => $older,
        ]);
        $second = Product::create([
            'name' => 'Second product',
            'slug' => 'second-product',
            'price' => 200000,
            'stock' => 2,
            'status' => 'active',
            'created_at' => $newer,
            'updated_at' => $newer,
        ]);

        ProductImage::create([
            'product_id' => $first->id,
            'image_path' => 'products/first-360.webp',
            'is_360' => true,
            'sort_order' => 0,
        ]);
        $firstCard = ProductImage::create([
            'product_id' => $first->id,
            'image_path' => 'products/first-card-low-id.webp',
            'is_360' => false,
            'sort_order' => 10,
        ]);
        ProductImage::create([
            'product_id' => $first->id,
            'image_path' => 'products/first-card-tie-high-id.webp',
            'is_360' => false,
            'sort_order' => 10,
        ]);
        ProductImage::create([
            'product_id' => $first->id,
            'image_path' => 'products/first-card-later.webp',
            'is_360' => false,
            'sort_order' => 20,
        ]);

        ProductImage::create([
            'product_id' => $second->id,
            'image_path' => 'products/second-card-later.webp',
            'is_360' => false,
            'sort_order' => 20,
        ]);
        ProductImage::create([
            'product_id' => $second->id,
            'image_path' => 'products/second-card.webp',
            'is_360' => false,
            'sort_order' => 5,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('page.module.items', 2));

        $this->actingAs($admin)
            ->get(route('admin.products.index', ['search' => $first->name]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.module.items.0.id', $first->id)
                ->where('page.module.items.0.image_url', url('/storage/'.$firstCard->image_path))
            );

        $this->actingAs($admin)
            ->get(route('admin.products.index', ['search' => $second->name]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.module.items.0.id', $second->id)
                ->where('page.module.items.0.image_url', url('/storage/products/second-card.webp'))
            );
    }
}
