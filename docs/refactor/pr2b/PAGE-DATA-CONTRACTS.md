# PR2B Page Data Contracts

All in-scope routes will return a single canonical `page` prop:

```php
[
    'page' => [
        'type' => '...',
        'seo' => [],
        'json_ld' => [],
        'breadcrumbs' => [],
        'hero' => [],
    ],
]
```

Global props remain owned by middleware:

- `site`
- `navigation`
- `auth`
- `cart`
- `flash`

## Product Index

- `page.type`: `product_index`
- `page.catalog.items`: normalized product cards
- `page.catalog.pagination`: normalized pagination metadata and links
- `page.catalog.filters`: `search`, `min_price`, `max_price`, `sort`
- `page.catalog.sort_options`: supported sort labels

## Product Detail

- `page.type`: `product_detail`
- `page.product`: normalized product detail contract
- `page.related_products`: normalized product cards
- Reviews expose only public fields.
- Review summary uses full approved aggregate, not the displayed rows.

## News Index

- `page.type`: `news_index`
- `page.news.items`: normalized post cards
- `page.news.pagination`: normalized pagination metadata and links
- `page.news.filters`: `search`, `category`
- `page.news.categories`: published-only category counts

## News Detail

- `page.type`: `news_detail`
- `page.post`: normalized post detail contract
- `page.related_posts`: deterministic same-category post cards

## About

- `page.type`: `about`
- `page.about.content_html`: sanitized rich content
- `page.about.mission`: optional
- `page.about.vision`: optional
- Contact data comes from global `site`, not duplicate raw settings.
