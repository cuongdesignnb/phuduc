<?php

namespace Tests\Feature\Admin;

use App\Models\MediaLibrary;
use App\Models\Product;
use App\Services\Admin\Catalog\ProductImageService;
use App\Services\Admin\Media\MediaAssetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MediaTypeSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_image_assets_are_rejected_by_the_shared_service(): void
    {
        foreach (['application/pdf', 'video/mp4'] as $mime) {
            $media = MediaLibrary::create(['file_name' => 'unsafe', 'file_path' => 'media/unsafe', 'mime_type' => $mime, 'size' => 1]);
            $this->expectException(ValidationException::class);
            app(MediaAssetService::class)->requireImage($media->id);
        }
    }

    public function test_product_attach_rejects_a_non_image_media_record(): void
    {
        $product = Product::create(['name' => 'Panel', 'slug' => 'panel', 'status' => 'active']);
        $media = MediaLibrary::create(['file_name' => 'manual.pdf', 'file_path' => 'media/manual.pdf', 'mime_type' => 'application/pdf', 'size' => 1]);

        $this->expectException(ValidationException::class);
        app(ProductImageService::class)->attach($product, $media, false);
    }

    public function test_post_featured_media_request_rejects_a_non_image(): void
    {
        $admin = $this->admin();
        $media = MediaLibrary::create(['file_name' => 'movie.mp4', 'file_path' => 'media/movie.mp4', 'mime_type' => 'video/mp4', 'size' => 1]);

        $this->actingAs($admin)->postJson(route('admin.posts.store'), ['title' => 'Tin', 'slug' => '', 'status' => 'draft', 'featured_media_id' => $media->id])
            ->assertUnprocessable()->assertJsonValidationErrors('featured_media_id');
    }

    private function admin()
    {
        return \App\Models\User::factory()->admin()->create();
    }
}
