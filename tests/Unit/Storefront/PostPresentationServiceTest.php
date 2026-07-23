<?php

namespace Tests\Unit\Storefront;

use App\Models\Post;
use App\Models\PostCategory;
use App\Services\Storefront\PostPresentationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostPresentationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_card_uses_normalized_media_and_backend_date(): void
    {
        $category = PostCategory::create(['name' => 'Guides', 'slug' => 'guides']);
        $post = Post::create([
            'post_category_id' => $category->id,
            'title' => 'Post',
            'slug' => 'post',
            'featured_image' => 'posts/post.webp',
            'status' => 'published',
            'created_at' => '2026-07-22 10:00:00',
        ])->load('category');

        $card = app(PostPresentationService::class)->card($post);

        $this->assertSame(url('/storage/posts/post.webp'), $card['image_url']);
        $this->assertSame('Guides', $card['category']['name']);
        $this->assertSame('22/07/2026', $card['published_at_display']);
    }
}
