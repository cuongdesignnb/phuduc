<?php

namespace Tests\Feature\Admin;

use App\Models\HomeSection;
use App\Models\Post;
use App\Services\Admin\Content\AdminPostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PostDeleteGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_referenced_by_home_content_cannot_be_deleted(): void
    {
        $post = Post::create(['title' => 'News', 'slug' => 'news', 'status' => 'published']);
        HomeSection::create(['key' => 'latest_posts', 'type' => 'post_collection', 'title' => 'News', 'variant' => 'editorial_grid', 'is_enabled' => true, 'sort_order' => 1, 'settings_json' => ['post_ids' => [$post->id]]]);
        $this->expectException(ValidationException::class);
        app(AdminPostService::class)->destroy($post);
    }
}
