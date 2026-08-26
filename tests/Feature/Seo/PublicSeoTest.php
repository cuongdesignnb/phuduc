<?php

namespace Tests\Feature\Seo;

use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_server_html_contains_critical_product_meta_without_client_javascript(): void
    {
        config(['app.name' => 'Phú Đức']);

        $product = Product::create([
            'name' => 'Xe điện chở hàng',
            'slug' => 'xe-dien-cho-hang',
            'description' => '<p>Mô tả <strong>an toàn</strong>.</p>',
            'meta_description' => '<b>META sạch</b> cho xe điện.',
            'price' => 100000,
            'stock' => 1,
            'status' => 'active',
        ]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('<title inertia>Xe điện chở hàng | Phú Đức</title>', false)
            ->assertSee('name="description" content="META sạch cho xe điện."', false)
            ->assertSee('rel="canonical" href="'.route('products.show', $product->slug).'"', false)
            ->assertSee('property="og:image" content="'.url('/og-default.svg').'"', false)
            ->assertSee('name="twitter:image" content="'.url('/og-default.svg').'"', false)
            ->assertSee('type="application/ld+json"', false)
            ->assertDontSee('name="keywords"', false)
            ->assertDontSee('Laravel');
    }

    public function test_catalog_pagination_self_canonicalizes_while_filter_and_sort_queries_are_noindex(): void
    {
        foreach (range(1, 13) as $index) {
            Product::create([
                'name' => "Sản phẩm {$index}",
                'slug' => "san-pham-{$index}",
                'price' => $index * 100,
                'status' => 'active',
            ]);
        }

        $this->get('/san-pham?page=2')
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.seo.canonical', route('products.index', ['page' => 2]))
                ->where('page.seo.robots', 'index, follow'));

        $this->get('/san-pham?sort=price_asc')
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.seo.canonical', route('products.index'))
                ->where('page.seo.robots', 'noindex, follow'));
    }

    public function test_news_query_and_pagination_follow_the_same_canonical_policy(): void
    {
        foreach (range(1, 13) as $index) {
            Post::create([
                'title' => "Tin {$index}",
                'slug' => "tin-{$index}",
                'summary' => 'Tóm tắt',
                'status' => 'published',
            ]);
        }

        $this->get('/tin-tuc?page=2')
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.seo.canonical', route('news.index', ['page' => 2]))
                ->where('page.seo.robots', 'index, follow'));

        $this->get('/tin-tuc?search=tin')
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.seo.canonical', route('news.index'))
                ->where('page.seo.robots', 'noindex, follow'));
    }

    public function test_published_post_uses_the_real_publication_timestamp_and_news_article_schema(): void
    {
        $publishedAt = now()->subDay()->startOfMinute();
        $author = User::factory()->create(['name' => 'Biên tập viên Phú Đức']);
        $post = Post::create([
            'title' => 'Bài viết được xuất bản',
            'slug' => 'bai-viet-duoc-xuat-ban',
            'summary' => 'Tóm tắt chính xác.',
            'status' => 'published',
            'author_id' => $author->id,
            'published_at' => $publishedAt,
        ]);

        $this->get(route('news.show', $post->slug))
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.seo.publishedTime', $publishedAt->toIso8601String())
                ->where('page.json_ld.0.@type', 'NewsArticle')
                ->where('page.json_ld.0.datePublished', $publishedAt->toIso8601String())
                ->where('page.json_ld.0.author.name', $author->name));
    }

    public function test_publishing_a_draft_sets_the_timestamp_once_and_keeps_it_on_later_edits(): void
    {
        $post = Post::create(['title' => 'Bản nháp', 'slug' => 'ban-nhap', 'status' => 'draft']);

        $post->update(['status' => 'published']);
        $publishedAt = $post->fresh()->published_at;

        $this->assertNotNull($publishedAt);

        $post->update(['title' => 'Bản nháp đã biên tập']);

        $this->assertTrue($publishedAt->equalTo($post->fresh()->published_at));
    }
}
