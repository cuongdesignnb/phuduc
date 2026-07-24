<?php

namespace App\Services\Admin\Content;

final class AdminSettingRegistry
{
    /** @return array<string, array<string, mixed>> */
    public static function all(): array
    {
        $groups = [
            'site' => ['name', 'tagline', 'description', 'logo', 'favicon', 'email', 'phone', 'hotline', 'address', 'working_hours', 'facebook', 'zalo', 'youtube', 'map_embed', 'primary_color'],
            'about' => ['title', 'content', 'image', 'mission', 'vision'],
            'seo' => ['default_title', 'default_description', 'default_keywords', 'og_image'],
            'appearance' => ['font.heading', 'font.body'],
        ];
        $definitions = [];
        foreach ($groups as $group => $keys) {
            foreach ($keys as $key) {
                $fullKey = str_contains($key, '.') ? $key : $group.'.'.$key;
                $shortKey = str_contains($key, '.') ? last(explode('.', $key)) : $key;
                $type = in_array($shortKey, ['logo', 'favicon', 'image', 'og_image'], true) ? 'image'
                    : ($shortKey === 'primary_color' ? 'color'
                        : (str_starts_with($fullKey, 'font.') ? 'font'
                            : (in_array($shortKey, ['description', 'content', 'mission', 'vision', 'default_description'], true) ? 'textarea' : 'text')));
                $definitions[$fullKey] = ['key' => $fullKey, 'label' => ucfirst(str_replace('_', ' ', $shortKey)), 'description' => '', 'group' => $group, 'type' => $type, 'default' => null, 'options' => []];
            }
        }
        return $definitions;
    }

    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }
}
