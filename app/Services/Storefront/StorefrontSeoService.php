<?php

namespace App\Services\Storefront;

class StorefrontSeoService
{
    public function __construct(
        private readonly SiteConfigurationService $siteConfiguration,
        private readonly SeoTextNormalizer $text,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function meta(array $overrides = []): array
    {
        $site = $this->siteConfiguration->get();
        $siteName = $this->text->normalize($site['name']) ?: 'Phú Đức';
        $title = $this->text->normalize($overrides['title'] ?? $siteName);

        if (($overrides['append_site'] ?? true) && ! $this->hasSiteSuffix($title, $siteName)) {
            $title .= ' | '.$siteName;
        }

        $robots = $site['prevent_indexing'] || $this->hasUnsafeProductionConfiguration()
            ? 'noindex, nofollow'
            : ($overrides['robots'] ?? 'index, follow');
        $description = $this->text->normalize(
            $overrides['description'] ?? $site['description'] ?? $site['tagline'] ?? "Thông tin chính thức từ {$siteName}.",
        );
        $canonical = array_key_exists('canonical', $overrides)
            ? ($overrides['canonical'] === null ? null : $this->absoluteUrl((string) $overrides['canonical']))
            : $this->absoluteUrl(url()->current());
        // Keep all public pages shareable even before an administrator uploads a
        // site-wide social image. A page-specific image still takes precedence.
        $ogImage = $overrides['ogImage'] ?? $site['og_image_url'] ?? url('/og-default.svg');

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => $robots,
            'ogTitle' => $overrides['ogTitle'] ?? $title,
            'ogDescription' => $overrides['ogDescription'] ?? $description,
            'ogImage' => $ogImage,
            'ogImageAlt' => $this->text->normalize($overrides['ogImageAlt'] ?? $overrides['title'] ?? $title),
            'ogType' => $overrides['ogType'] ?? 'website',
            'ogUrl' => array_key_exists('ogUrl', $overrides)
                ? $overrides['ogUrl']
                : $canonical,
            'siteName' => $siteName,
            'locale' => 'vi_VN',
            'twitterCard' => $overrides['twitterCard'] ?? 'summary_large_image',
            'twitterTitle' => $overrides['twitterTitle'] ?? $title,
            'twitterDescription' => $overrides['twitterDescription'] ?? $description,
            'twitterImage' => $overrides['twitterImage'] ?? $ogImage,
            'twitterImageAlt' => $this->text->normalize($overrides['twitterImageAlt'] ?? $overrides['ogImageAlt'] ?? $overrides['title'] ?? $title),
            'publishedTime' => $overrides['publishedTime'] ?? null,
            'modifiedTime' => $overrides['modifiedTime'] ?? null,
            'section' => $overrides['section'] ?? null,
        ];
    }

    /**
     * @param  list<array{name: string, url?: string}>  $breadcrumbs
     * @return array<string, mixed>
     */
    public function breadcrumbJsonLd(array $breadcrumbs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($breadcrumbs)
                ->map(fn (array $item, int $index) => array_filter([
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['name'],
                    'item' => $item['url'] ?? null,
                ]))
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    public function productJsonLd(array $product): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product['name'],
            'description' => $this->text->normalize($product['description_html'] ?? ''),
            'sku' => $product['sku'],
            'url' => route('products.show', $product['slug']),
        ];

        $images = collect($product['gallery'] ?? [])->pluck('url')->filter()->values()->all();
        if ($images !== []) {
            $schema['image'] = $images;
        }

        $variantPrices = collect($product['variants'] ?? [])
            ->pluck('price')
            ->map(fn ($price) => (float) $price)
            ->filter(fn (float $price) => $price > 0)
            ->values();

        $variantStocks = collect($product['variants'] ?? [])->pluck('stock')->map(fn ($stock) => (int) $stock);
        $availability = $variantStocks->isNotEmpty()
            ? ($variantStocks->contains(fn (int $stock) => $stock > 0) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock')
            : ((int) ($product['stock'] ?? 0) > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock');

        if ($variantPrices->isNotEmpty()) {
            $schema['offers'] = $variantPrices->count() > 1
                ? [
                    '@type' => 'AggregateOffer',
                    'lowPrice' => $variantPrices->min(),
                    'highPrice' => $variantPrices->max(),
                    'offerCount' => $variantPrices->count(),
                    'priceCurrency' => 'VND',
                    'availability' => $availability,
                    'url' => route('products.show', $product['slug']),
                ]
                : [
                    '@type' => 'Offer',
                    'price' => $variantPrices->first(),
                    'priceCurrency' => 'VND',
                    'availability' => $availability,
                    'url' => route('products.show', $product['slug']),
                ];
        } elseif (($product['price'] ?? 0) > 0) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'price' => $product['price'],
                'priceCurrency' => 'VND',
                'availability' => $availability,
                'url' => route('products.show', $product['slug']),
            ];
        }

        $summary = $product['review_summary'] ?? [];
        if (
            ($summary['count'] ?? 0) > 0
            && ($summary['average_rating'] ?? null) !== null
            && ($summary['visible_count'] ?? 0) >= ($summary['count'] ?? 0)
        ) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $summary['average_rating'],
                'reviewCount' => $summary['count'],
                'bestRating' => $summary['best_rating'] ?? 5,
                'worstRating' => 1,
            ];
        }

        return array_filter($schema, fn ($value) => $value !== null && $value !== '');
    }

    /**
     * @param  array<string, mixed>  $post
     * @return array<string, mixed>
     */
    public function articleJsonLd(array $post): array
    {
        $site = $this->siteConfiguration->get();

        $publisher = array_filter([
            '@type' => 'Organization',
            'name' => $site['name'],
            'url' => url('/'),
            'logo' => $site['logo_url'],
        ]);

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $post['title'],
            'description' => $this->text->normalize($post['summary'] ?? $post['content_html'] ?? '', 200),
            'url' => route('news.show', $post['slug']),
            'datePublished' => $post['published_at'] ?? null,
            'dateModified' => $post['updated_at'] ?? null,
            'image' => $post['image_url'] ?? null,
            'author' => $post['author'] ?? null,
            'publisher' => $publisher,
        ], fn ($value) => $value !== null && $value !== '' && $value !== []);
    }

    /**
     * @return array<string, mixed>
     */
    public function organizationJsonLd(): array
    {
        $site = $this->siteConfiguration->get();
        $telephone = $site['hotline'] ?: $site['phone'];
        $hasContact = filled($telephone) || filled($site['email']);
        $hasAddress = filled($site['address']);
        $contactPoint = array_filter([
            '@type' => 'ContactPoint',
            'telephone' => $telephone,
            'email' => $site['email'],
            'contactType' => 'customer service',
        ]);
        $address = array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $site['address'],
            'addressCountry' => 'VN',
        ]);

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $site['name'],
            'url' => url('/'),
            'logo' => $site['logo_url'],
            'contactPoint' => $hasContact ? $contactPoint : null,
            'address' => $hasAddress ? $address : null,
            'sameAs' => collect($site['social_links'])->filter()->values()->all() ?: null,
        ], fn ($value) => $value !== null && $value !== '' && $value !== []);
    }

    /**
     * @return array<string, mixed>
     */
    public function websiteJsonLd(): array
    {
        $site = $this->siteConfiguration->get();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $this->text->normalize($site['name']) ?: 'Phú Đức',
            'url' => url('/'),
            'inLanguage' => 'vi-VN',
        ];
    }

    private function hasSiteSuffix(string $title, string $siteName): bool
    {
        return $title === $siteName || str_ends_with(mb_strtolower($title), ' | '.mb_strtolower($siteName));
    }

    private function absoluteUrl(string $url): string
    {
        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            return url($url);
        }

        return $url;
    }

    private function hasUnsafeProductionConfiguration(): bool
    {
        return app()->environment('production')
            && (! str_starts_with((string) config('app.url'), 'https://')
                || strcasecmp((string) config('app.name'), 'Laravel') === 0);
    }
}
