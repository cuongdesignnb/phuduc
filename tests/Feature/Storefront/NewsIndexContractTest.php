<?php

namespace Tests\Feature\Storefront;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NewsIndexContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_index_returns_cards_categories_and_filters(): void
    {
        $category = PostCategory::create(['name' => 'Guides', 'slug' => 'guides']);
        Post::create(['post_category_id' => $category->id, 'title' => 'Battery Guide', 'slug' => 'battery-guide', 'summary' => 'Battery', 'status' => 'published']);
        Post::create(['title' => 'Draft', 'slug' => 'draft', 'status' => 'draft']);

        $this->get('/tin-tuc?search=Battery&category=guides')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Guest/News/Index')
                ->where('page.type', 'news_index')
                ->has('page.news.items', 1)
                ->where('page.news.items.0.slug', 'battery-guide')
                ->where('page.news.items.0.category.slug', 'guides')
                ->where('page.news.categories.0.posts_count', 1)
                ->where('page.news.filters.search', 'Battery')
                ->where('page.seo.robots', 'noindex, follow')
                ->missing('posts')
            );
    }
}
