<?php

namespace App\Services\Storefront;

class StorefrontPageService
{
    public function __construct(
        private readonly SiteConfigurationService $siteConfiguration,
        private readonly NavigationService $navigation,
        private readonly HomePageDataService $homePage,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function home(): array
    {
        $site = $this->siteConfiguration->get();
        $description = $site['description'] ?: $site['tagline'] ?: "Trang thông tin chính thức của {$site['name']}.";

        return [
            'site' => $site,
            'navigation' => $this->navigation->get(),
            'page' => [
                'type' => 'home',
                'seo' => [
                    'title' => $site['name'],
                    'description' => $description,
                    'canonical' => url('/'),
                    'robots' => $site['prevent_indexing'] ? 'noindex, nofollow' : 'index, follow',
                ],
                'json_ld' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'Organization',
                    'name' => $site['name'],
                    'url' => url('/'),
                    'logo' => $site['logo_url'],
                    'email' => $site['email'],
                    'telephone' => $site['hotline'] ?: $site['phone'],
                    'address' => $site['address'],
                ],
                'sections' => $this->homePage->sections(),
            ],
        ];
    }
}
