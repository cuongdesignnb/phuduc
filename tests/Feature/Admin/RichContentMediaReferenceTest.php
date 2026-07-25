<?php

namespace Tests\Feature\Admin;

use App\Models\MediaLibrary;
use App\Models\Post;
use App\Models\Product;
use App\Services\Admin\Media\MediaReferenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RichContentMediaReferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_embedded_post_and_product_images_block_media_deletion(): void
    {
        $media = MediaLibrary::create(['file_name' => 'embedded.webp', 'file_path' => 'media/embedded.webp', 'mime_type' => 'image/webp', 'size' => 1]);
        Post::create(['title' => 'Bài viết', 'slug' => 'bai-viet', 'status' => 'published', 'content' => '<p><img src="/storage/media/embedded.webp"></p>']);
        Product::create(['name' => 'Sản phẩm', 'slug' => 'san-pham', 'status' => 'active', 'description' => '<img src="https://phuduc.test/storage/media/embedded.webp">']);

        $references = app(MediaReferenceService::class)->references($media);

        $this->assertFalse(app(MediaReferenceService::class)->canDelete($media));
        $this->assertSame(1, collect($references)->firstWhere('type', 'post_content')['count']);
        $this->assertSame(1, collect($references)->firstWhere('type', 'product_description')['count']);
    }
}
