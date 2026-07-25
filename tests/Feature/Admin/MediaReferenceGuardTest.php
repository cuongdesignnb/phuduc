<?php

namespace Tests\Feature\Admin;

use App\Models\MediaLibrary;
use App\Models\Post;
use App\Services\Admin\Media\MediaReferenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaReferenceGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_referenced_by_post_is_not_deletable(): void
    {
        $media = MediaLibrary::create(['file_name' => 'cover.jpg', 'file_path' => 'media/cover.jpg', 'mime_type' => 'image/jpeg', 'size' => 1]);
        Post::create(['title' => 'Post', 'slug' => 'post', 'status' => 'published', 'featured_image' => $media->file_path]);
        $this->assertFalse(app(MediaReferenceService::class)->canDelete($media));
    }
}
