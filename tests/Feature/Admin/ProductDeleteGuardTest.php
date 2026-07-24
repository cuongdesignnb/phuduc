<?php

namespace Tests\Feature\Admin;

use App\Models\HomeSection;
use App\Models\Product;
use App\Services\Admin\Catalog\AdminProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductDeleteGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_referenced_by_home_content_cannot_be_deleted(): void
    {
        $product = Product::create(['name' => 'Panel', 'slug' => 'panel', 'status' => 'active']);
        HomeSection::create(['key' => 'featured_products', 'type' => 'product_collection', 'title' => 'Featured', 'variant' => 'marketplace_grid', 'is_enabled' => true, 'sort_order' => 1, 'settings_json' => ['product_ids' => [$product->id]]]);
        $this->expectException(ValidationException::class);
        app(AdminProductService::class)->destroy($product);
    }
}
