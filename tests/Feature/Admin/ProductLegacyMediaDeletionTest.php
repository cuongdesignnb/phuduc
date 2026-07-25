<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Admin\Catalog\AdminProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductLegacyMediaDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_deletion_removes_owned_files_but_preserves_legacy_shared_paths(): void
    {
        Storage::fake('public');
        $product = Product::create(['name' => 'Tấm pin', 'slug' => 'tam-pin', 'status' => 'active']);
        $owned = "products/{$product->id}/owned.webp";
        $legacy = 'legacy/shared.webp';
        Storage::disk('public')->put($owned, 'owned');
        Storage::disk('public')->put($legacy, 'legacy');
        ProductImage::create(['product_id' => $product->id, 'image_path' => $owned, 'sort_order' => 0]);
        ProductImage::create(['product_id' => $product->id, 'image_path' => $legacy, 'sort_order' => 1]);

        app(AdminProductService::class)->destroy($product);

        Storage::disk('public')->assertMissing($owned);
        Storage::disk('public')->assertExists($legacy);
        $this->assertDatabaseCount('product_images', 0);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
