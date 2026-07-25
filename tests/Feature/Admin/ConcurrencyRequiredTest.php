<?php

namespace Tests\Feature\Admin;

use App\Models\Menu;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcurrencyRequiredTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_update_requires_a_version(): void
    {
        $product = Product::create(['name' => 'Tấm pin', 'slug' => 'tam-pin', 'status' => 'active']);
        $this->actingAs($this->admin())->putJson(route('admin.products.update', $product), $this->productPayload())->assertUnprocessable()->assertJsonValidationErrors('version');
    }

    public function test_post_update_requires_a_version(): void
    {
        $post = Post::create(['title' => 'Bài viết', 'slug' => 'bai-viet', 'status' => 'draft']);
        $this->actingAs($this->admin())->putJson(route('admin.posts.update', $post), ['title' => 'Bài viết mới', 'slug' => 'bai-viet-moi', 'status' => 'draft'])->assertUnprocessable()->assertJsonValidationErrors('version');
    }

    public function test_menu_details_and_structure_require_a_version(): void
    {
        $menu = Menu::create(['name' => 'Header', 'location' => 'header']);
        $admin = $this->admin();
        $this->actingAs($admin)->putJson(route('admin.menus.update', $menu), ['name' => 'Header mới', 'location' => 'header'])->assertUnprocessable()->assertJsonValidationErrors('version');
        $this->actingAs($admin)->postJson(route('admin.menus.items', $menu), ['items' => []])->assertUnprocessable()->assertJsonValidationErrors('version');
    }

    public function test_home_content_and_settings_require_a_version(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->postJson(route('admin.home-content.save'), ['sections' => [[
            'key' => 'hero', 'type' => 'hero', 'enabled' => true, 'sort_order' => 0, 'variant' => 'split',
            'heading' => ['eyebrow' => null, 'title' => 'Trang chủ', 'subtitle' => null, 'description' => null],
            'config' => ['image' => null], 'items' => [],
        ]]])->assertUnprocessable()->assertJsonValidationErrors('version');
        $this->actingAs($admin)->postJson(route('admin.settings.save'), ['settings' => []])->assertUnprocessable()->assertJsonValidationErrors('version');
    }

    private function productPayload(): array
    {
        return ['name' => 'Tấm pin mới', 'slug' => 'tam-pin-moi', 'sku' => '', 'description' => '', 'price' => 0, 'stock' => 0, 'status' => 'active', 'specifications' => []];
    }

    private function admin(): \App\Models\User
    {
        return \App\Models\User::factory()->admin()->create();
    }
}
