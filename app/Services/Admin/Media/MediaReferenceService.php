<?php

namespace App\Services\Admin\Media;

use App\Models\HomeSection;
use App\Models\HomeSectionItem;
use App\Models\MediaLibrary;
use App\Models\Post;
use App\Models\ProductImage;
use App\Models\Setting;
use Illuminate\Support\Str;

class MediaReferenceService
{
    public function normalize(?string $path): string
    {
        return ltrim(Str::replaceFirst('/storage/', '', (string) $path), '/');
    }

    public function resolvePath(int $mediaId): string
    {
        return (string) MediaLibrary::query()->findOrFail($mediaId)->file_path;
    }

    public function idForPath(?string $path): ?int
    {
        $normalized = $this->normalize($path);

        return $normalized === ''
            ? null
            : MediaLibrary::query()->where('file_path', $normalized)->value('id');
    }

    /**
     * @return array<int, array{type: string, count: int}>
     */
    public function references(MediaLibrary $media): array
    {
        $path = $this->normalize($media->file_path);
        $references = [];
        $productImages = ProductImage::query()->where('image_path', $path)->count();
        $posts = Post::query()->where('featured_image', $path)->count();
        $settings = Setting::query()
            ->whereIn('key', $this->imageSettingKeys())
            ->get(['key', 'value'])
            ->filter(fn (Setting $setting) => $this->containsPath($setting->value, $path))
            ->count();
        $homeSections = HomeSection::query()->get(['key', 'settings_json'])
            ->filter(fn (HomeSection $section) => $this->containsPath($section->settings_json, $path))
            ->count();
        $homeItems = HomeSectionItem::query()->where('image', $path)->count();

        foreach ([
            'product_images' => $productImages,
            'posts' => $posts,
            'settings' => $settings,
            'home_sections' => $homeSections,
            'home_section_items' => $homeItems,
        ] as $type => $count) {
            if ($count > 0) {
                $references[] = ['type' => $type, 'count' => $count];
            }
        }

        return $references;
    }

    public function canDelete(MediaLibrary $media): bool
    {
        return $this->references($media) === [];
    }

    /** @return list<string> */
    private function imageSettingKeys(): array
    {
        return ['site.logo', 'site.favicon', 'about.image', 'seo.og_image'];
    }

    private function containsPath(mixed $value, string $path): bool
    {
        if (is_array($value)) {
            foreach ($value as $child) {
                if ($this->containsPath($child, $path)) {
                    return true;
                }
            }

            return false;
        }

        return is_string($value) && $this->normalize($value) === $path;
    }
}
