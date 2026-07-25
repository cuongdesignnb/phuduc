<?php

namespace Tests\Feature\Admin;

use App\Services\Admin\Content\AdminSettingService;
use App\Services\Admin\Content\AdminUrlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
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

    public function test_page_envelope_options_statuses_and_menu_locations_are_vietnamese(): void
    {
        $admin = \App\Models\User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.posts.create'))->assertInertia(fn ($page) => $page
            ->where('page.meta.title', 'Thêm bài viết')
            ->where('page.module.statuses.0.label', 'Bản nháp')
            ->where('page.module.statuses.1.label', 'Đã đăng'));
        $this->actingAs($admin)->get(route('admin.menus.create'))->assertInertia(fn ($page) => $page
            ->where('page.module.locations.header.label', 'Đầu trang')
            ->where('page.module.locations.footer.label', 'Chân trang'));
    }

    public function test_success_and_validation_messages_are_vietnamese(): void
    {
        $admin = \App\Models\User::factory()->admin()->create();
        $this->actingAs($admin)->post(route('admin.menus.store'), ['name' => 'Menu mới', 'location' => 'header'])
            ->assertSessionHas('success', 'Menu đã được tạo.');

        $version = app(AdminSettingService::class)->page($admin)['page']['module']['version'];
        $response = $this->actingAs($admin)->postJson(route('admin.settings.save'), [
            'version' => $version,
            'settings' => [['key' => 'site.email', 'value' => 'not-an-email', 'type' => 'email']],
        ]);
        $response->assertUnprocessable();
        $this->assertStringContainsString('Email không hợp lệ.', (string) $response->json('errors.settings.0'));

        try {
            app(AdminUrlService::class)->normalize('javascript:alert(1)');
            $this->fail('Unsafe URL should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertSame('URL menu không an toàn.', $exception->errors()['items'][0]);
        }
    }
}
