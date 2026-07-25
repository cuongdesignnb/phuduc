<?php

namespace Tests\Feature\Admin;

use App\Models\HomeSection;
use App\Models\MediaLibrary;
use App\Services\Admin\Content\AdminHomeContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class HomeContentImagePathIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_raw_home_image_path_without_a_media_id_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        app(AdminHomeContentService::class)->save($this->hero(['image' => 'media/untrusted.webp']));
    }

    public function test_media_id_is_the_canonical_home_image_reference_and_null_clears_it(): void
    {
        $media = MediaLibrary::create(['file_name' => 'hero.webp', 'file_path' => 'media/hero.webp', 'mime_type' => 'image/webp', 'size' => 1]);
        $service = app(AdminHomeContentService::class);
        $service->save($this->hero(['image' => 'legacy.webp', 'image_media_id' => $media->id]));
        $this->assertSame('media/hero.webp', HomeSection::query()->firstOrFail()->settings_json['image']);

        $service->save($this->hero(['image' => null, 'image_media_id' => null]));
        $this->assertNull(HomeSection::query()->firstOrFail()->settings_json['image']);
    }

    private function hero(array $config): array
    {
        return ['sections' => [[
            'key' => 'hero', 'type' => 'hero', 'enabled' => true, 'sort_order' => 0, 'variant' => 'split',
            'heading' => ['eyebrow' => null, 'title' => 'Trang chủ', 'subtitle' => null, 'description' => null],
            'config' => $config, 'items' => [],
        ]]];
    }
}
