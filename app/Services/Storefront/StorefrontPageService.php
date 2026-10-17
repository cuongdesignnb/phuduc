<?php

namespace App\Services\Storefront;

class StorefrontPageService
{
    public function __construct(
        private readonly SiteConfigurationService $siteConfiguration,
        private readonly NavigationService $navigation,
        private readonly HomePageDataService $homePage,
        private readonly StorefrontSeoService $seo,
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
                'seo' => $this->seo->meta([
                    'title' => $site['name'],
                    'description' => $description,
                    'canonical' => url('/'),
                    'append_site' => false,
                ]),
                'json_ld' => [
                    $this->seo->organizationJsonLd(),
                    $this->seo->websiteJsonLd(),
                ],
                'sections' => $this->homePage->sections(),
            ],
        ];
    }
}
