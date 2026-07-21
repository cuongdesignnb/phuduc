<?php

namespace Database\Seeders;

use App\Models\HomeSection;
use App\Models\HomeSectionItem;
use App\Support\Homepage\HomeSectionRegistry;
use Illuminate\Database\Seeder;

class HomeContentSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'hero' => [
                'title' => 'GIẢI PHÁP XE ĐIỆN & THIẾT BỊ ĐIỆN CÔNG NGHIỆP',
                'subtitle' => 'Hiệu suất mạnh mẽ - Vận hành bền bỉ - Tiết kiệm năng lượng',
                'sort_order' => 10,
                'settings_json' => [
                    'image' => null,
                    'primary_cta' => ['label' => 'Xem sản phẩm', 'url' => '/san-pham'],
                    'secondary_cta' => ['label' => 'Tư vấn ngay', 'action' => 'phone'],
                ],
            ],
            'category_cards' => ['title' => 'Danh mục nổi bật', 'sort_order' => 20],
            'benefit_strip' => ['title' => 'Cam kết dịch vụ', 'sort_order' => 30],
            'featured_products' => [
                'title' => 'Sản phẩm nổi bật',
                'sort_order' => 40,
                'settings_json' => ['source' => 'manual', 'limit' => 4, 'product_ids' => []],
            ],
            'energy_banner' => [
                'title' => 'Cho tương lai bền vững',
                'description' => 'Giải pháp xe điện giúp tối ưu chi phí vận hành và giảm phát thải.',
                'sort_order' => 50,
                'settings_json' => ['eyebrow' => 'Năng lượng xanh', 'image' => null, 'stats' => []],
            ],
            'industry_solutions' => ['title' => 'Giải pháp theo ngành', 'sort_order' => 60],
            'testimonials' => ['title' => 'Khách hàng nói về chúng tôi', 'sort_order' => 70],
            'partners' => ['title' => 'Đối tác tiêu biểu', 'sort_order' => 80],
            'latest_posts' => [
                'title' => 'Tin tức mới nhất',
                'sort_order' => 90,
                'settings_json' => ['source' => 'latest', 'limit' => 3, 'post_ids' => []],
            ],
            'consultation_steps' => ['title' => 'Tư vấn giải pháp đúng nhu cầu', 'sort_order' => 100],
        ];

        $newSectionKeys = [];

        foreach ($defaults as $key => $values) {
            $definition = HomeSectionRegistry::get($key);
            $section = HomeSection::firstOrCreate(
                ['key' => $key],
                [
                    'type' => $definition['type'],
                    'title' => $values['title'],
                    'subtitle' => $values['subtitle'] ?? null,
                    'description' => $values['description'] ?? null,
                    'variant' => $definition['allowed_variants'][0],
                    'is_enabled' => true,
                    'sort_order' => $values['sort_order'],
                    'settings_json' => $values['settings_json'] ?? $definition['defaults'],
                ],
            );

            if ($section->wasRecentlyCreated) {
                $newSectionKeys[] = $key;
            }
        }

        $this->seedInitialItems($newSectionKeys);
    }

    /**
     * @param  list<string>  $newSectionKeys
     */
    private function seedInitialItems(array $newSectionKeys): void
    {
        $items = [
            'category_cards' => [
                ['title' => 'Xe điện', 'subtitle' => 'Xem ngay', 'icon' => 'cart', 'url' => '/san-pham', 'metadata_json' => ['tone' => 'cart']],
                ['title' => 'Cầu điện', 'subtitle' => 'Xem ngay', 'icon' => 'crane', 'url' => '/san-pham', 'metadata_json' => ['tone' => 'crane']],
                ['title' => 'Xe nâng điện', 'subtitle' => 'Xem ngay', 'icon' => 'forklift', 'url' => '/san-pham', 'metadata_json' => ['tone' => 'forklift']],
            ],
            'benefit_strip' => [
                ['title' => 'Chính hãng 100%', 'icon' => 'shield'],
                ['title' => 'Bảo hành dài hạn', 'icon' => 'award'],
                ['title' => 'Giao hàng toàn quốc', 'icon' => 'truck'],
                ['title' => 'Hỗ trợ kỹ thuật 24/7', 'icon' => 'headset'],
            ],
            'consultation_steps' => [
                ['title' => '01', 'subtitle' => 'Khảo sát nhu cầu', 'description' => 'Khảo sát tải trọng, tuyến vận hành và tần suất sử dụng.', 'metadata_json' => ['tone' => 'yellow']],
                ['title' => '02', 'subtitle' => 'Đề xuất giải pháp', 'description' => 'Đề xuất xe, pin, phụ kiện và phương án bảo trì.', 'metadata_json' => ['tone' => 'blue']],
                ['title' => '03', 'subtitle' => 'Bàn giao', 'description' => 'Đào tạo vận hành và hỗ trợ kỹ thuật.', 'metadata_json' => ['tone' => 'green']],
            ],
        ];

        foreach ($items as $sectionKey => $sectionItems) {
            if (! in_array($sectionKey, $newSectionKeys, true)) {
                continue;
            }

            $section = HomeSection::query()->where('key', $sectionKey)->firstOrFail();
            foreach ($sectionItems as $index => $item) {
                HomeSectionItem::firstOrCreate(
                    ['home_section_id' => $section->id, 'title' => $item['title']],
                    [
                        'section_key' => $sectionKey,
                        'subtitle' => $item['subtitle'] ?? null,
                        'description' => $item['description'] ?? null,
                        'image' => $item['image'] ?? null,
                        'icon' => $item['icon'] ?? null,
                        'url' => $item['url'] ?? null,
                        'metadata_json' => $item['metadata_json'] ?? null,
                        'is_active' => true,
                        'sort_order' => $index,
                    ],
                );
            }
        }
    }
}
