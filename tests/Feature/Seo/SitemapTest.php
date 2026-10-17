<?php

namespace Tests\Feature\Seo;

use App\Models\Post;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_only_contains_canonical_indexable_content(): void
    {
        $active = Product::create(['name' => 'Đang bán', 'slug' => 'dang-ban', 'status' => 'active']);
        Product::create(['name' => 'Ngừng bán', 'slug' => 'ngung-ban', 'status' => 'inactive']);
        $post = Post::create(['title' => 'Đã đăng', 'slug' => 'da-dang', 'status' => 'published']);
        Post::create(['title' => 'Bản nháp', 'slug' => 'ban-nhap', 'status' => 'draft']);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset', false)
            ->assertSee(route('products.show', $active->slug), false)
            ->assertSee(route('news.show', $post->slug), false)
            ->assertDontSee('ngung-ban')
            ->assertDontSee('ban-nhap')
            ->assertDontSee('gio-hang')
            ->assertDontSee('?page=');
    }

    public function test_dynamic_robots_declares_the_absolute_sitemap_url(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Sitemap: '.route('sitemap'), false);
    }
}
