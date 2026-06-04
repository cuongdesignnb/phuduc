<?php

namespace App\Services;

use App\Models\Setting;

class SeoService
{
    /**
     * Generate SEO meta data for a page.
     */
    public static function meta(array $overrides = []): array
    {
        $siteName = Setting::get('site.name', config('app.name'));
        $defaults = [
            'title' => $siteName,
            'description' => Setting::get('site.description', ''),
            'ogImage' => Setting::get('site.og_image', ''),
            'ogType' => 'website',
            'canonical' => url()->current(),
            'robots' => 'index, follow',
        ];

        $meta = array_merge($defaults, array_filter($overrides));

        // Append site name to title if it's not the homepage
        if (!empty($overrides['title'])) {
            $meta['title'] = $overrides['title'] . ' | ' . $siteName;
        }

        return $meta;
    }

    /**
     * Generate JSON-LD for a Product.
     */
    public static function productJsonLd(object $product): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => strip_tags($product->description ?? ''),
            'sku' => $product->sku ?? '',
            'url' => url("/san-pham/{$product->slug}"),
        ];

        // Image
        if ($product->images && count($product->images) > 0) {
            $schema['image'] = collect($product->images)
                ->where('is_360', false)
                ->sortBy('sort_order')
                ->pluck('image_path')
                ->map(fn($path) => url("storage/{$path}"))
                ->values()
                ->toArray();
        }

        // Price / Offers
        if ($product->price > 0) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'price' => $product->price,
                'priceCurrency' => 'VND',
                'availability' => ($product->stock > 0)
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'url' => url("/san-pham/{$product->slug}"),
            ];
        }

        // Reviews aggregate
        if (isset($product->approvedReviews) && count($product->approvedReviews) > 0) {
            $reviews = collect($product->approvedReviews);
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => round($reviews->avg('rating'), 1),
                'reviewCount' => $reviews->count(),
                'bestRating' => 5,
                'worstRating' => 1,
            ];
        }

        return $schema;
    }

    /**
     * Generate JSON-LD for an Article/Blog post.
     */
    public static function articleJsonLd(object $post): array
    {
        $siteName = Setting::get('site.name', config('app.name'));

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->title,
            'description' => strip_tags($post->summary ?? $post->content ?? ''),
            'url' => url("/tin-tuc/{$post->slug}"),
            'datePublished' => $post->created_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'publisher' => [
                '@type' => 'Organization',
                'name' => $siteName,
                'url' => url('/'),
            ],
        ];

        if (!empty($post->featured_image)) {
            $schema['image'] = url("storage/{$post->featured_image}");
        }

        // Truncate description for SEO
        if (isset($schema['description'])) {
            $schema['description'] = mb_substr($schema['description'], 0, 200);
        }

        return $schema;
    }

    /**
     * Generate JSON-LD for Organization (About / Homepage).
     */
    public static function organizationJsonLd(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => Setting::get('site.name', config('app.name')),
            'url' => url('/'),
            'logo' => Setting::get('site.logo') ? url('storage/' . Setting::get('site.logo')) : '',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => Setting::get('site.phone', ''),
                'email' => Setting::get('site.email', ''),
                'contactType' => 'customer service',
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => Setting::get('site.address', ''),
                'addressCountry' => 'VN',
            ],
        ];
    }

    /**
     * Generate BreadcrumbList JSON-LD.
     */
    public static function breadcrumbJsonLd(array $items): array
    {
        $listItems = [];
        foreach ($items as $i => $item) {
            $listItems[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => $item['url'] ?? null,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $listItems,
        ];
    }
}
