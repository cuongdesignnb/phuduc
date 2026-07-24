<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Models\User;
use App\Services\Admin\Content\AdminUrlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MenuTargetAndUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_target_endpoints_return_compact_dtos_and_hydrate_ids(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::create(['name' => 'Solar Panel', 'slug' => 'solar-panel', 'sku' => 'SP-1', 'status' => 'active']);
        $post = Post::create(['title' => 'Tin mới', 'slug' => 'tin-moi', 'status' => 'published']);
        $category = PostCategory::create(['name' => 'Tin tức', 'slug' => 'tin-tuc']);

        $this->actingAs($admin)->getJson(route('admin.menu-targets.products', ['ids' => [$product->id], 'limit' => 99]))->assertOk()->assertJsonPath('items.0.id', $product->id)->assertJsonStructure(['items' => [['id', 'label', 'status']]]);
        $this->actingAs($admin)->getJson(route('admin.menu-targets.posts', ['ids' => [$post->id]]))->assertOk()->assertJsonPath('items.0.id', $post->id);
        $this->actingAs($admin)->getJson(route('admin.menu-targets.categories', ['ids' => [$category->id]]))->assertOk()->assertJsonPath('items.0.id', $category->id);
    }

    public function test_url_schemes_are_validated_by_type(): void
    {
        $urls = app(AdminUrlService::class);
        $this->assertSame('mailto:test@example.com?subject=Hello', $urls->normalize('mailto:test@example.com?subject=Hello'));
        $this->assertSame('tel:+84 901-234-567', $urls->normalize('tel:+84 901-234-567'));
        foreach (['#', 'javascript:alert(1)', 'data:text/html,x', 'file:///tmp/a', '//evil.example', 'tel:abc'] as $value) {
            try {
                $urls->normalize($value);
                $this->fail("Unsafe URL accepted: {$value}");
            } catch (ValidationException) {
                $this->addToAssertionCount(1);
            }
        }
    }
}
