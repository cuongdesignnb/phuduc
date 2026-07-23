<?php

namespace Tests\Feature\Storefront;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NewsDetailContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_detail_returns_article_contract_and_related_posts(): void
    {
        $category = PostCategory::create(['name' => 'Guides', 'slug' => 'guides']);
        $post = Post::create([
            'post_category_id' => $category->id,
            'title' => 'Article',
            'slug' => 'article',
            'content' => '<p>Body</p><script>alert(1)</script>',
            'featured_image' => 'posts/article.webp',
            'status' => 'published',
        ]);
        Post::create(['post_category_id' => $category->id, 'title' => 'Related', 'slug' => 'related', 'status' => 'published', 'created_at' => now()->addMinute()]);
        Post::create(['post_category_id' => $category->id, 'title' => 'Draft', 'slug' => 'draft', 'status' => 'draft']);

        $this->get('/tin-tuc/'.$post->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Guest/News/Show')
                ->where('page.type', 'news_detail')
                ->where('page.post.image_url', url('/storage/posts/article.webp'))
                ->where('page.post.category.slug', 'guides')
                ->where('page.related_posts.0.slug', 'related')
                ->missing('post')
            );
    }
}
