<?php

namespace Tests\Feature\Admin;

use App\Models\MediaLibrary;
use App\Models\Product;
use App\Services\Admin\Catalog\ProductImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_attaching_media_creates_owned_product_copy(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/source.jpg', 'image');
        $media = MediaLibrary::create(['file_name' => 'source.jpg', 'file_path' => 'media/source.jpg', 'mime_type' => 'image/jpeg', 'size' => 5]);
        $product = Product::create(['name' => 'Panel', 'slug' => 'panel', 'status' => 'active']);
        $image = app(ProductImageService::class)->attach($product, $media, false);
        $this->assertStringStartsWith('products/'.$product->id.'/', $image->image_path);
        Storage::disk('public')->assertExists($image->image_path);
    }
}
