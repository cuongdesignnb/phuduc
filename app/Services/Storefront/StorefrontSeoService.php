<?php

namespace App\Services\Storefront;

class StorefrontSeoService
{
    public function __construct(private readonly SiteConfigurationService $siteConfiguration) {}

    /**
     * @return array<string, mixed>
     */
    public function meta(array $overrides = []): array
    {
        $site = $this->siteConfiguration->get();
        $title = $overrides['title'] ?? $site['name'];

        if (($overrides['append_site'] ?? true) && $title !== $site['name']) {
            $title .= ' | '.$site['name'];
        }

        return [
            'title' => $title,
            'description' => $overrides['description'] ?? $site['description'],
            'ogImage' => $overrides['ogImage'] ?? $site['og_image_url'],
            'ogType' => $overrides['ogType'] ?? 'website',
            'canonical' => array_key_exists('canonical', $overrides)
                ? $overrides['canonical']
                : url()->current(),
            'robots' => $overrides['robots'] ?? 'index, follow',
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
            'description' => trim(strip_tags((string) ($product['description_html'] ?? ''))),
            'sku' => $product['sku'],
            'url' => route('products.show', $product['slug']),
        ];

        $images = collect($product['gallery'] ?? [])->pluck('url')->filter()->values()->all();
        if ($images !== []) {
            $schema['image'] = $images;
        }

        if (($product['price'] ?? 0) > 0) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'price' => $product['price'],
                'priceCurrency' => 'VND',
                'availability' => ((int) ($product['stock'] ?? 0) > 0)
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'url' => route('products.show', $product['slug']),
            ];
        }

        $summary = $product['review_summary'] ?? [];
        if (($summary['count'] ?? 0) > 0 && ($summary['average_rating'] ?? null) !== null) {
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
            '@type' => 'Article',
            'headline' => $post['title'],
            'description' => mb_substr(strip_tags((string) ($post['summary'] ?? $post['content_html'] ?? '')), 0, 200),
            'url' => route('news.show', $post['slug']),
            'datePublished' => $post['published_at'] ?? null,
            'dateModified' => $post['updated_at'] ?? null,
            'image' => $post['image_url'] ?? null,
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
}
