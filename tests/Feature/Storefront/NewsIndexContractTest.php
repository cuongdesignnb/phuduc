<?php

namespace Tests\Feature\Storefront;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('literalLikeSearchTokens')]
    public function test_news_search_treats_like_metacharacters_as_literals(string $label, string $token): void
    {
        $post = Post::create([
            'title' => "Literal {$label} news {$token}",
            'slug' => "literal-{$label}",
            'summary' => 'A matching news summary.',
            'status' => 'published',
        ]);
        Post::create([
            'title' => 'Ordinary news without the search token',
            'slug' => "ordinary-{$label}",
            'summary' => 'An ordinary news summary.',
            'status' => 'published',
        ]);

        $this->get('/tin-tuc?search='.rawurlencode($token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('page.news.items', 1)
                ->where('page.news.items.0.slug', $post->slug)
                ->where('page.news.filters.search', $token)
            );
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function literalLikeSearchTokens(): array
    {
        return [
            'underscore' => ['underscore', '_'],
            'percent' => ['percent', '%'],
            'exclamation' => ['exclamation', '!'],
        ];
    }

    public function test_valid_category_query_is_noindex_and_canonicalizes_to_the_news_index(): void
    {
        $category = PostCategory::create(['name' => 'Guides', 'slug' => 'guides']);
        Post::create(['post_category_id' => $category->id, 'title' => 'Battery Guide', 'slug' => 'battery-guide', 'summary' => 'Battery', 'status' => 'published']);

        $this->get('/tin-tuc?category=guides')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.news.filters.category', 'guides')
                ->where('page.seo.canonical', route('news.index'))
                ->where('page.seo.robots', 'noindex, follow')
            );
    }

    public function test_invalid_category_returns_404(): void
    {
        $this->get('/tin-tuc?category=slug-khong-ton-tai')->assertNotFound();
    }

    public function test_search_remains_noindex(): void
    {
        $this->get('/tin-tuc?search=Battery')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.seo.robots', 'noindex, follow')
                ->where('page.seo.canonical', route('news.index'))
            );
    }

    public function test_draft_only_category_count_is_zero(): void
    {
        $category = PostCategory::create(['name' => 'Drafts', 'slug' => 'drafts']);
        Post::create(['post_category_id' => $category->id, 'title' => 'Draft', 'slug' => 'draft', 'status' => 'draft']);

        $this->get('/tin-tuc')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.news.categories.0.slug', 'drafts')
                ->where('page.news.categories.0.posts_count', 0)
            );
    }
}
