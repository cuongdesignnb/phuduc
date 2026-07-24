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
        return $this->idsForPaths([$path])[$this->normalize($path)] ?? null;
    }

    /** @param list<?string> $paths @return array<string, int> */
    public function idsForPaths(array $paths): array
    {
        $normalized = collect($paths)->map(fn ($path) => $this->normalize($path))->filter()->unique()->values()->all();
        if ($normalized === []) {
            return [];
        }

        return MediaLibrary::query()->whereIn('file_path', $normalized)->pluck('id', 'file_path')
            ->map(fn ($id) => (int) $id)->all();
    }

    /**
     * @return array<int, array{type: string, count: int}>
     */
    public function references(MediaLibrary $media): array
    {
        return $this->forPaths([$media->file_path])[$this->normalize($media->file_path)] ?? [];
    }

    /** @param list<?string> $paths @return array<string, list<array{type: string, count: int}>> */
    public function forPaths(array $paths): array
    {
        $normalized = collect($paths)->map(fn ($path) => $this->normalize($path))->filter()->unique()->values()->all();
        $result = array_fill_keys($normalized, []);
        if ($normalized === []) {
            return $result;
        }

        $counts = [];
        foreach (ProductImage::query()->whereIn('image_path', $normalized)->selectRaw('image_path, count(*) as aggregate')->groupBy('image_path')->get() as $row) {
            $counts[$this->normalize($row->image_path)]['product_images'] = (int) $row->aggregate;
        }
        foreach (Post::query()->whereIn('featured_image', $normalized)->selectRaw('featured_image, count(*) as aggregate')->groupBy('featured_image')->get() as $row) {
            $counts[$this->normalize($row->featured_image)]['posts'] = (int) $row->aggregate;
        }
        foreach (HomeSectionItem::query()->whereIn('image', $normalized)->selectRaw('image, count(*) as aggregate')->groupBy('image')->get() as $row) {
            $counts[$this->normalize($row->image)]['home_section_items'] = (int) $row->aggregate;
        }
        foreach (Setting::query()->whereIn('key', $this->imageSettingKeys())->get(['key', 'value']) as $setting) {
            foreach ($normalized as $path) {
                if ($this->containsPath($setting->value, $path)) {
                    $counts[$path]['settings'] = ($counts[$path]['settings'] ?? 0) + 1;
                }
            }
        }
        foreach (HomeSection::query()->get(['settings_json']) as $section) {
            foreach ($normalized as $path) {
                if ($this->containsPath($section->settings_json, $path)) {
                    $counts[$path]['home_sections'] = ($counts[$path]['home_sections'] ?? 0) + 1;
                }
            }
        }
        foreach ($counts as $path => $types) {
            foreach ($types as $type => $count) {
                if ($count > 0) {
                    $result[$path][] = ['type' => $type, 'count' => $count];
                }
            }
        }

        return $result;
    }

    public function canDelete(MediaLibrary $media): bool
    {
        return $this->references($media) === [];
    }

    /** @return list<string> */
    private function imageSettingKeys(): array
    {
        return ['site.logo', 'site.favicon', 'about.image', 'site.og_image'];
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
