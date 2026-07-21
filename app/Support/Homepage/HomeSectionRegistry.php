<?php

namespace App\Support\Homepage;

final class HomeSectionRegistry
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function definitions(): array
    {
        return [
            'hero' => self::section(
                type: 'hero',
                label: 'Hero',
                variants: ['industrial_marketplace', 'split'],
                headingFields: ['title', 'subtitle', 'description'],
                configKeys: ['image', 'primary_cta', 'secondary_cta'],
                configFields: [
                    ['key' => 'image', 'label' => 'Ảnh', 'control' => 'media'],
                    ['key' => 'primary_cta.label', 'label' => 'Nhãn CTA chính', 'control' => 'text'],
                    ['key' => 'primary_cta.url', 'label' => 'URL CTA chính', 'control' => 'text'],
                    ['key' => 'secondary_cta.label', 'label' => 'Nhãn CTA phụ', 'control' => 'text'],
                    ['key' => 'secondary_cta.action', 'label' => 'Hành động CTA phụ', 'control' => 'select', 'options' => ['phone', 'url']],
                    ['key' => 'secondary_cta.url', 'label' => 'URL CTA phụ', 'control' => 'text'],
                ],
            ),
            'category_cards' => self::section(
                type: 'item_collection',
                label: 'Danh mục',
                variants: ['cards', 'compact_cards'],
                headingFields: ['title', 'subtitle'],
                supportsItems: true,
                itemFields: ['title', 'subtitle', 'image', 'icon', 'url', 'tone'],
            ),
            'benefit_strip' => self::section(
                type: 'item_collection',
                label: 'Cam kết dịch vụ',
                variants: ['icon_strip'],
                headingFields: [],
                supportsItems: true,
                itemFields: ['title', 'icon'],
            ),
            'featured_products' => self::section(
                type: 'product_collection',
                label: 'Sản phẩm nổi bật',
                variants: ['marketplace_grid', 'compact_grid'],
                headingFields: ['title', 'subtitle'],
                configKeys: ['source', 'limit', 'product_ids'],
                configFields: [
                    ['key' => 'source', 'label' => 'Nguồn dữ liệu', 'control' => 'select', 'options' => ['manual', 'latest']],
                    ['key' => 'limit', 'label' => 'Số sản phẩm', 'control' => 'number'],
                    ['key' => 'product_ids', 'label' => 'Sản phẩm đã chọn', 'control' => 'product_picker'],
                ],
                defaults: ['source' => 'manual', 'limit' => 4, 'product_ids' => []],
            ),
            'energy_banner' => self::section(
                type: 'content_banner',
                label: 'Banner năng lượng',
                variants: ['green_energy'],
                headingFields: ['title', 'description'],
                configKeys: ['eyebrow', 'image', 'stats'],
                configFields: [
                    ['key' => 'eyebrow', 'label' => 'Nhãn nhỏ', 'control' => 'text'],
                    ['key' => 'image', 'label' => 'Ảnh', 'control' => 'media'],
                    ['key' => 'stats.0.label', 'label' => 'Nhãn thống kê 1', 'control' => 'text'],
                    ['key' => 'stats.0.value', 'label' => 'Giá trị thống kê 1', 'control' => 'text'],
                    ['key' => 'stats.1.label', 'label' => 'Nhãn thống kê 2', 'control' => 'text'],
                    ['key' => 'stats.1.value', 'label' => 'Giá trị thống kê 2', 'control' => 'text'],
                ],
            ),
            'industry_solutions' => self::section(
                type: 'item_collection',
                label: 'Giải pháp theo ngành',
                variants: ['industry_grid'],
                headingFields: ['title'],
                supportsItems: true,
                itemFields: ['title', 'image', 'url', 'tone'],
            ),
            'testimonials' => self::section(
                type: 'item_collection',
                label: 'Khách hàng nói gì',
                variants: ['quote_cards'],
                headingFields: ['title'],
                supportsItems: true,
                itemFields: ['title', 'subtitle', 'description', 'image', 'avatar_text'],
            ),
            'partners' => self::section(
                type: 'item_collection',
                label: 'Đối tác',
                variants: ['logo_grid'],
                headingFields: ['title'],
                supportsItems: true,
                itemFields: ['title', 'image', 'url'],
            ),
            'latest_posts' => self::section(
                type: 'post_collection',
                label: 'Tin tức mới nhất',
                variants: ['editorial_grid', 'compact_grid'],
                headingFields: ['title'],
                configKeys: ['source', 'limit', 'post_ids'],
                configFields: [
                    ['key' => 'source', 'label' => 'Nguồn dữ liệu', 'control' => 'select', 'options' => ['manual', 'latest']],
                    ['key' => 'limit', 'label' => 'Số bài viết', 'control' => 'number'],
                    ['key' => 'post_ids', 'label' => 'Bài viết đã chọn', 'control' => 'post_picker'],
                ],
                defaults: ['source' => 'latest', 'limit' => 3, 'post_ids' => []],
            ),
            'consultation_steps' => self::section(
                type: 'item_collection',
                label: 'Các bước tư vấn',
                variants: ['numbered_steps'],
                headingFields: ['title'],
                supportsItems: true,
                itemFields: ['title', 'subtitle', 'description', 'tone'],
            ),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function get(string $key): ?array
    {
        return self::definitions()[$key] ?? null;
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::definitions());
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultSection(string $key, int $sortOrder): array
    {
        $definition = self::get($key);

        return [
            'key' => $key,
            'type' => $definition['type'],
            'title' => $definition['label'],
            'subtitle' => null,
            'description' => null,
            'variant' => $definition['allowed_variants'][0],
            'is_enabled' => true,
            'sort_order' => $sortOrder,
            'settings_json' => $definition['defaults'],
        ];
    }

    /**
     * @param  list<string>  $variants
     * @param  list<string>  $configKeys
     * @param  list<array<string, mixed>>  $configFields
     * @param  list<string>  $itemFields
     * @param  list<string>  $headingFields
     * @param  array<string, mixed>  $defaults
     * @return array<string, mixed>
     */
    private static function section(
        string $type,
        string $label,
        array $variants,
        bool $supportsItems = false,
        array $configKeys = [],
        array $configFields = [],
        array $itemFields = [],
        array $headingFields = ['title'],
        array $defaults = [],
    ): array {
        return [
            'type' => $type,
            'label' => $label,
            'supports_items' => $supportsItems,
            'allowed_variants' => $variants,
            'config_keys' => $configKeys,
            'config_fields' => $configFields,
            'item_fields' => $itemFields,
            'heading_fields' => $headingFields,
            'defaults' => $defaults,
        ];
    }
}
