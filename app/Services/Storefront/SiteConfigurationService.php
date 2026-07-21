<?php

namespace App\Services\Storefront;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class SiteConfigurationService
{
    /**
     * Request-scoped cache shared by the Inertia middleware and page service.
     *
     * @var array<string, mixed>|null
     */
    private ?array $configuration = null;

    public const KEYS = [
        'site.name',
        'site.tagline',
        'site.description',
        'site.logo',
        'site.favicon',
        'site.phone',
        'site.hotline',
        'site.email',
        'site.address',
        'site.working_hours',
        'site.facebook',
        'site.zalo',
        'site.youtube',
        'site.og_image',
        'site.copyright',
        'site.primary_color',
        'font.heading',
        'font.body',
    ];

    public function __construct(
        private readonly MediaUrlService $mediaUrl,
        private readonly ThemeTokenService $themeTokens,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function get(): array
    {
        if ($this->configuration !== null) {
            return $this->configuration;
        }

        $settings = Schema::hasTable('settings')
            ? Setting::query()->whereIn('key', self::KEYS)->pluck('value', 'key')
            : collect();

        return $this->configuration = $this->fromSettings($settings);
    }

    /**
     * @param  Collection<string, string|null>  $settings
     * @return array<string, mixed>
     */
    public function fromSettings(Collection $settings): array
    {
        $value = fn (string $key, mixed $default = null): mixed => filled($settings->get($key))
            ? $settings->get($key)
            : $default;

        $name = $value('site.name', config('app.name', 'Phú Đức'));

        $theme = $this->themeTokens->resolve(
            $value('site.primary_color'),
            $value('font.heading'),
            $value('font.body'),
        );

        return [
            'name' => $name,
            'tagline' => $value('site.tagline'),
            'description' => $value('site.description'),
            'logo_url' => $this->mediaUrl->resolve($value('site.logo')),
            'favicon_url' => $this->mediaUrl->resolve($value('site.favicon')),
            'phone' => $value('site.phone'),
            'hotline' => $value('site.hotline'),
            'email' => $value('site.email'),
            'address' => $value('site.address'),
            'working_hours' => $value('site.working_hours'),
            'copyright' => $value('site.copyright', '© '.date('Y').' '.$name.'. All rights reserved.'),
            'social_links' => [
                'facebook' => $value('site.facebook'),
                'zalo' => $value('site.zalo'),
                'youtube' => $value('site.youtube'),
            ],
            'og_image_url' => $this->mediaUrl->resolve($value('site.og_image')),
            'theme' => $theme,
            // Temporary Admin compatibility. New public components use site.theme.
            'primary_color' => $theme['primary_color'],
            'fonts' => $theme['fonts'],
        ];
    }
}
