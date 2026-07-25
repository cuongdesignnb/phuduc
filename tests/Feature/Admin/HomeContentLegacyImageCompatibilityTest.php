<?php

namespace Tests\Feature\Admin;

use App\Models\HomeSection;
use App\Models\HomeSectionItem;
use App\Models\User;
use App\Services\Admin\Content\AdminHomeContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class HomeContentLegacyImageCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_contract_marks_legacy_section_and_item_images(): void
    {
        [$section, $item] = $this->legacyRecords();
        $module = app(AdminHomeContentService::class)->page(User::factory()->admin()->create())['page']['module'];
        $hero = collect($module['sections'])->firstWhere('key', 'hero');
        $partners = collect($module['sections'])->firstWhere('key', 'partners');

        $this->assertSame('legacy/hero.webp', $hero['config']['image']);
        $this->assertNull($hero['config']['image_media_id']);
        $this->assertTrue($hero['config']['image_is_legacy']);
        $this->assertSame($section->id, $hero['id']);
        $this->assertSame($item->id, $partners['items'][0]['id']);
        $this->assertSame('legacy/partner.webp', $partners['items'][0]['image']);
        $this->assertNull($partners['items'][0]['media_id']);
        $this->assertTrue($partners['items'][0]['image_is_legacy']);
    }

    public function test_unchanged_legacy_paths_survive_save_and_unrelated_changes(): void
    {
        [$section, $item] = $this->legacyRecords();
        $admin = User::factory()->admin()->create();
        $service = app(AdminHomeContentService::class);

        $service->save($this->payload($admin, [
            $this->section('hero', 'hero', 'split', ['image' => 'legacy/hero.webp'], []),
            $this->section('partners', 'item_collection', 'logo_grid', [], [$this->item($item->id, 'legacy/partner.webp')]),
        ]));
        $this->assertSame('legacy/hero.webp', $section->refresh()->settings_json['image']);
        $this->assertSame('legacy/partner.webp', $item->refresh()->image);

        $service->save($this->payload($admin, [
            $this->section('hero', 'hero', 'split', ['primary_cta' => ['label' => 'Liên hệ']], []),
        ]));
        $this->assertSame('legacy/hero.webp', $section->refresh()->settings_json['image']);
    }

    public function test_changed_raw_legacy_path_is_rejected(): void
    {
        $this->legacyRecords();
        $admin = User::factory()->admin()->create();

        $this->expectException(ValidationException::class);
        app(AdminHomeContentService::class)->save($this->payload($admin, [
            $this->section('hero', 'hero', 'split', ['image' => 'legacy/changed.webp'], []),
        ]));
    }

    private function legacyRecords(): array
    {
        $section = HomeSection::create([
            'key' => 'hero', 'type' => 'hero', 'title' => 'Trang chủ', 'variant' => 'split',
            'is_enabled' => true, 'sort_order' => 0, 'settings_json' => ['image' => 'legacy/hero.webp'],
        ]);
        $partners = HomeSection::create([
            'key' => 'partners', 'type' => 'item_collection', 'title' => 'Đối tác', 'variant' => 'logo_grid',
            'is_enabled' => true, 'sort_order' => 10, 'settings_json' => [],
        ]);
        $item = HomeSectionItem::create([
            'home_section_id' => $partners->id, 'section_key' => 'partners', 'title' => 'Đối tác cũ',
            'image' => 'legacy/partner.webp', 'is_active' => true, 'sort_order' => 0,
        ]);

        return [$section, $item];
    }

    private function payload(User $admin, array $sections): array
    {
        return ['version' => app(AdminHomeContentService::class)->page($admin)['page']['module']['version'], 'sections' => $sections];
    }

    private function section(string $key, string $type, string $variant, array $config, array $items): array
    {
        return [
            'key' => $key, 'type' => $type, 'enabled' => true, 'sort_order' => $key === 'hero' ? 0 : 10,
            'variant' => $variant, 'heading' => ['title' => ucfirst($key), 'subtitle' => null, 'description' => null],
            'config' => $config, 'items' => $items,
        ];
    }

    private function item(int $id, string $image): array
    {
        return ['id' => $id, 'title' => 'Đối tác cũ', 'image' => $image, 'url' => null, 'metadata' => [], 'enabled' => true, 'sort_order' => 0];
    }
}
