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

    public function test_attaching_an_album_preserves_media_order_in_owned_product_copies(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/first.jpg', 'image-one');
        Storage::disk('public')->put('media/second.jpg', 'image-two');
        $first = MediaLibrary::create(['file_name' => 'first.jpg', 'file_path' => 'media/first.jpg', 'mime_type' => 'image/jpeg', 'size' => 9]);
        $second = MediaLibrary::create(['file_name' => 'second.jpg', 'file_path' => 'media/second.jpg', 'mime_type' => 'image/jpeg', 'size' => 9]);
        $product = Product::create(['name' => 'Album panel', 'slug' => 'album-panel', 'status' => 'active']);

        $images = app(ProductImageService::class)->attachMany($product, [$second->id, $first->id], false);

        $this->assertSame([1, 2], collect($images)->map(fn ($image) => $image->sort_order)->all());
        $this->assertSame('image-two', Storage::disk('public')->get($images[0]->image_path));
        $this->assertSame('image-one', Storage::disk('public')->get($images[1]->image_path));
        $this->assertCount(2, $product->images()->get());
    }
}
