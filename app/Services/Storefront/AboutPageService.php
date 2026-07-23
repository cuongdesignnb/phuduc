<?php

namespace App\Services\Storefront;

use App\Models\Setting;

class AboutPageService
{
    private const KEYS = [
        'about.title',
        'about.description',
        'about.content',
        'about.mission',
        'about.vision',
    ];

    public function __construct(
        private readonly SiteConfigurationService $siteConfiguration,
        private readonly StorefrontSeoService $seo,
        private readonly RichHtmlSanitizer $sanitizer,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function page(): array
    {
        $site = $this->siteConfiguration->get();
        $settings = Setting::query()->whereIn('key', self::KEYS)->pluck('value', 'key');
        $title = $settings->get('about.title') ?: 'Gioi thieu';
        $description = $settings->get('about.description') ?: $site['description'];
        $breadcrumbs = [
            ['name' => 'Trang chu', 'url' => url('/')],
            ['name' => 'Gioi thieu', 'url' => route('about')],
        ];

        return [
            'page' => [
                'type' => 'about',
                'seo' => $this->seo->meta([
                    'title' => $title,
                    'description' => $description,
                    'canonical' => route('about'),
                ]),
                'json_ld' => [
                    $this->seo->organizationJsonLd(),
                    $this->seo->breadcrumbJsonLd($breadcrumbs),
                ],
                'breadcrumbs' => $breadcrumbs,
                'hero' => [
                    'eyebrow' => $site['name'],
                    'title' => $title,
                    'description' => $description,
                ],
                'about' => [
                    'content_html' => $this->sanitizer->sanitize($settings->get('about.content')),
                    'mission' => $settings->get('about.mission'),
                    'vision' => $settings->get('about.vision'),
                ],
            ],
        ];
    }
}
