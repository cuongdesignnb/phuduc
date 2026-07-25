<?php

namespace Tests\Feature\Admin;

use App\Models\Menu;
use App\Models\Post;
use App\Models\Product;
use App\Services\Admin\AdminConcurrencyService;
use App\Services\Admin\Content\AdminSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class VietnameseFrameworkValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_framework_messages_are_vietnamese(): void
    {
        $admin = $this->admin();

        $this->assertMessageContains(
            $this->actingAs($admin)->postJson(route('admin.products.store'), ['status' => 'active']),
            'name',
            'tên là bắt buộc',
        );
        $this->assertMessageContains(
            $this->actingAs($admin)->postJson(route('admin.products.store'), ['name' => 'Tấm pin', 'price' => 'abc', 'status' => 'active']),
            'price',
            'giá phải là một số nguyên',
        );

        $product = Product::create(['name' => 'Tấm pin', 'slug' => 'tam-pin', 'status' => 'active']);
        $this->assertMessageContains(
            $this->actingAs($admin)->putJson(route('admin.products.update', $product), ['name' => 'Tấm pin mới', 'status' => 'active']),
            'version',
            'phiên bản dữ liệu là bắt buộc',
        );
    }

    public function test_post_framework_messages_are_vietnamese(): void
    {
        $admin = $this->admin();

        $this->assertMessageContains(
            $this->actingAs($admin)->postJson(route('admin.posts.store'), ['status' => 'draft']),
            'title',
            'tiêu đề là bắt buộc',
        );
        $this->assertMessageContains(
            $this->actingAs($admin)->postJson(route('admin.posts.store'), ['title' => 'Bài viết', 'status' => 'invalid']),
            'status',
            'trạng thái đã chọn không hợp lệ',
        );

        $post = Post::create(['title' => 'Bài viết', 'slug' => 'bai-viet', 'status' => 'draft']);
        $this->assertMessageContains(
            $this->actingAs($admin)->putJson(route('admin.posts.update', $post), ['title' => 'Bài viết mới', 'status' => 'draft']),
            'version',
            'phiên bản dữ liệu là bắt buộc',
        );
    }

    public function test_nested_menu_framework_messages_keep_the_exact_path(): void
    {
        $admin = $this->admin();
        $menu = Menu::create(['name' => 'Header', 'location' => 'header']);
        $version = app(AdminConcurrencyService::class)->version($menu);

        $response = $this->actingAs($admin)->postJson(route('admin.menus.items', $menu), [
            'version' => $version,
            'items' => [[
                'client_key' => 'root',
                'title' => 'Gốc',
                'model_type' => 'url',
                'url' => '/',
                'children' => [['client_key' => 'child', 'model_type' => 'url', 'url' => '/', 'children' => []]],
            ]],
        ]);
        $this->assertMessageContains($response, 'items.0.children.0.title', 'Tên mục menu là bắt buộc');

        $this->assertMessageContains(
            $this->actingAs($admin)->postJson(route('admin.menus.items', $menu), [
                'version' => $version,
                'items' => [['id' => '1', 'client_key' => 'root-2', 'title' => 'Gốc', 'model_type' => 'url', 'url' => '/', 'children' => []]],
            ]),
            'items.0.id',
            'ID mục menu phải là số nguyên',
        );

        $deep = ['client_key' => 'one', 'title' => 'Một', 'model_type' => 'url', 'url' => '/', 'children' => []];
        for ($i = 2; $i <= 5; $i++) {
            $deep = ['client_key' => "node-$i", 'title' => "Mục $i", 'model_type' => 'url', 'url' => '/', 'children' => [$deep]];
        }
        $this->assertMessageContains(
            $this->actingAs($admin)->postJson(route('admin.menus.items', $menu), ['version' => $version, 'items' => [$deep]]),
            'items.0.children.0.children.0.children.0.children',
            'Menu chỉ được phép tối đa 4 cấp',
        );

        $this->assertMessageContains(
            $this->actingAs($admin)->postJson(route('admin.menus.items', $menu), ['items' => []]),
            'version',
            'phiên bản dữ liệu là bắt buộc',
        );
    }

    public function test_media_framework_messages_are_vietnamese(): void
    {
        $admin = $this->admin();

        $this->assertMessageContains(
            $this->actingAs($admin)->withHeaders(['Accept' => 'application/json'])->post(route('admin.media.store'), ['files' => [UploadedFile::fake()->create('bad.txt', 10, 'text/plain')]]),
            'files.0',
            'phải là tệp thuộc một trong các loại MIME',
        );

        $files = array_map(fn (int $index) => UploadedFile::fake()->image("image-$index.jpg"), range(1, 21));
        $this->assertMessageContains(
            $this->actingAs($admin)->withHeaders(['Accept' => 'application/json'])->post(route('admin.media.store'), ['files' => $files]),
            'files',
            'không được có quá 20 phần tử',
        );

        $this->assertMessageContains(
            $this->actingAs($admin)->withHeaders(['Accept' => 'application/json'])->post(route('admin.media.store'), ['files' => [UploadedFile::fake()->image('large.jpg')->size(10241)]]),
            'files.0',
            'không được lớn hơn 10240 kilobyte',
        );
    }

    public function test_home_framework_messages_are_vietnamese(): void
    {
        $admin = $this->admin();
        $section = $this->homeSection('hero');

        $this->assertMessageContains(
            $this->actingAs($admin)->postJson(route('admin.home-content.save'), ['sections' => [$section]]),
            'version',
            'phiên bản dữ liệu là bắt buộc',
        );

        $invalidKey = [...$section, 'key' => 'unknown'];
        $this->assertMessageContains(
            $this->actingAs($admin)->postJson(route('admin.home-content.save'), ['version' => 'test', 'sections' => [$invalidKey]]),
            'sections.0.key',
            'khóa phần nội dung đã chọn không hợp lệ',
        );

        $invalidMedia = [...$section, 'config' => [...$section['config'], 'image_media_id' => 999999]];
        $this->assertMessageContains(
            $this->actingAs($admin)->postJson(route('admin.home-content.save'), ['version' => 'test', 'sections' => [$invalidMedia]]),
            'sections.0.config.image_media_id',
            'ID hình ảnh đã chọn không tồn tại',
        );
    }

    public function test_settings_service_messages_are_vietnamese(): void
    {
        $admin = $this->admin();
        $version = app(AdminSettingService::class)->page($admin)['page']['module']['version'];

        $this->assertMessageContains(
            $this->actingAs($admin)->postJson(route('admin.settings.save'), ['settings' => []]),
            'version',
            'phiên bản dữ liệu là bắt buộc',
        );
        $this->assertMessageContains(
            $this->actingAs($admin)->postJson(route('admin.settings.save'), ['version' => $version, 'settings' => [['key' => 'site.email', 'value' => 'not-an-email']]]),
            'settings',
            'Email không hợp lệ',
        );
        $this->assertMessageContains(
            $this->actingAs($admin)->postJson(route('admin.settings.save'), ['version' => $version, 'settings' => [['key' => 'site.primary_color', 'value' => '#zzzzzz']]]),
            'settings',
            'Màu phải là mã hex hợp lệ',
        );
        $this->assertMessageContains(
            $this->actingAs($admin)->postJson(route('admin.settings.save'), ['version' => $version, 'settings' => [['key' => 'unknown.key', 'value' => 'value']]]),
            'settings',
            'Cài đặt chưa được đăng ký trong registry',
        );
    }

    /** @param array<string, mixed> $payload */
    private function assertMessageContains(TestResponse $response, string $key, string $expected): void
    {
        $response->assertUnprocessable();
        $message = (string) ($response->json('errors')[$key][0] ?? '');
        $this->assertStringContainsString($expected, $message, "Expected a Vietnamese validation message for $key, got: $message");
        $this->assertStringNotContainsString('The ', $message);
    }

    /** @return array<string, mixed> */
    private function homeSection(string $key): array
    {
        return [
            'key' => $key,
            'type' => 'hero',
            'enabled' => true,
            'sort_order' => 0,
            'variant' => 'split',
            'heading' => ['eyebrow' => null, 'title' => 'Trang chủ', 'subtitle' => null, 'description' => null],
            'config' => ['image' => null],
            'items' => [],
        ];
    }

    private function admin(): \App\Models\User
    {
        return \App\Models\User::factory()->admin()->create();
    }
}
