<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVietnameseContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_admin_pages_expose_vietnamese_titles(): void
    {
        $admin = \App\Models\User::factory()->admin()->create();
        foreach ([
            'admin.products.index' => 'Sản phẩm',
            'admin.posts.index' => 'Bài viết',
            'admin.post-categories.index' => 'Danh mục tin',
            'admin.menus.index' => 'Menu',
            'admin.home-content.index' => 'Nội dung trang chủ',
            'admin.settings.index' => 'Cài đặt',
        ] as $routeName => $title) {
            $this->actingAs($admin)->get(route($routeName))->assertOk()->assertInertia(fn ($page) => $page->where('page.meta.title', $title));
        }
    }
}
