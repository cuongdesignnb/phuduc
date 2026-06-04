<?php

namespace Database\Seeders;

use App\Models\HomeSection;
use App\Models\HomeSectionItem;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class HomeContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $this->seedSections();
    }

    private function seedSettings(): void
    {
        $settings = [
            ['key' => 'home.hero_title', 'value' => "GIẢI PHÁP XE ĐIỆN &\nTHIẾT BỊ ĐIỆN CÔNG NGHIỆP", 'type' => 'textarea'],
            ['key' => 'home.hero_subtitle', 'value' => 'Hiệu suất mạnh mẽ - Vận hành bền bỉ - Tiết kiệm năng lượng - Thân thiện môi trường', 'type' => 'textarea'],
            ['key' => 'home.hero_primary_label', 'value' => 'Xem sản phẩm', 'type' => 'text'],
            ['key' => 'home.hero_primary_url', 'value' => '/san-pham', 'type' => 'text'],
            ['key' => 'home.hero_secondary_label', 'value' => 'Tư vấn ngay', 'type' => 'text'],
            ['key' => 'home.featured_products_title', 'value' => 'Sản phẩm nổi bật', 'type' => 'text'],
            ['key' => 'home.featured_products_limit', 'value' => '4', 'type' => 'text'],
            ['key' => 'home.latest_posts_title', 'value' => 'Tin tức nổi bật', 'type' => 'text'],
            ['key' => 'home.latest_posts_limit', 'value' => '3', 'type' => 'text'],
            ['key' => 'home.energy_eyebrow', 'value' => 'Năng lượng xanh', 'type' => 'text'],
            ['key' => 'home.energy_title', 'value' => 'Cho tương lai bền vững', 'type' => 'text'],
            ['key' => 'home.energy_description', 'value' => 'Sản phẩm xe điện & thiết bị điện công nghiệp của PHÚ ĐỨC BIKE giúp doanh nghiệp tối ưu chi phí vận hành, giảm phát thải và nâng cao hiệu suất sản xuất.', 'type' => 'textarea'],
            ['key' => 'home.energy_stat_1_label', 'value' => 'Tiết kiệm năng lượng', 'type' => 'text'],
            ['key' => 'home.energy_stat_1_value', 'value' => '30-50%', 'type' => 'text'],
            ['key' => 'home.energy_stat_2_label', 'value' => 'Giảm phát thải CO₂', 'type' => 'text'],
            ['key' => 'home.energy_stat_2_value', 'value' => '> 60%', 'type' => 'text'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'type' => $setting['type']],
            );
        }
    }

    private function seedSections(): void
    {
        $sections = [
            'category_cards' => [
                'title' => 'Danh mục nổi bật',
                'sort_order' => 10,
                'items' => [
                    ['title' => 'Xe điện', 'subtitle' => 'Xem ngay', 'icon' => 'cart', 'url' => '/san-pham', 'metadata_json' => ['tone' => 'cart']],
                    ['title' => 'Cầu điện', 'subtitle' => 'Xem ngay', 'icon' => 'crane', 'url' => '/san-pham', 'metadata_json' => ['tone' => 'crane']],
                    ['title' => 'Xe nâng điện', 'subtitle' => 'Xem ngay', 'icon' => 'forklift', 'url' => '/san-pham', 'metadata_json' => ['tone' => 'forklift']],
                ],
            ],
            'benefits' => [
                'title' => 'Cam kết dịch vụ',
                'sort_order' => 20,
                'items' => [
                    ['title' => 'Chính hãng 100%', 'icon' => 'shield'],
                    ['title' => 'Bảo hành dài hạn', 'icon' => 'award'],
                    ['title' => 'Giao hàng toàn quốc', 'icon' => 'truck'],
                    ['title' => 'Hỗ trợ kỹ thuật 24/7', 'icon' => 'headset'],
                    ['title' => 'Trả góp linh hoạt', 'icon' => 'box'],
                ],
            ],
            'industry_solutions' => [
                'title' => 'Giải pháp theo ngành',
                'sort_order' => 30,
                'items' => [
                    ['title' => 'Kho vận - Logistics', 'url' => '/san-pham', 'metadata_json' => ['tone' => 'warehouse']],
                    ['title' => 'Nhà máy - Sản xuất', 'url' => '/san-pham', 'metadata_json' => ['tone' => 'factory']],
                    ['title' => 'Công trường xây dựng', 'url' => '/san-pham', 'metadata_json' => ['tone' => 'site']],
                    ['title' => 'Nông nghiệp - Trang trại', 'url' => '/san-pham', 'metadata_json' => ['tone' => 'farm']],
                    ['title' => 'Nội bộ - Khu công nghiệp', 'url' => '/san-pham', 'metadata_json' => ['tone' => 'campus']],
                ],
            ],
            'testimonials' => [
                'title' => 'Khách hàng nói về chúng tôi',
                'sort_order' => 40,
                'items' => [
                    [
                        'title' => 'Anh Nguyễn Văn Hùng',
                        'subtitle' => 'Quản lý vận hành - Công ty TNHH Megatech',
                        'description' => 'Sản phẩm chất lượng, vận hành ổn định, tiết kiệm điện. Dịch vụ hậu mãi và hỗ trợ kỹ thuật rất nhanh chóng và chuyên nghiệp.',
                        'metadata_json' => ['avatar_text' => 'A'],
                    ],
                ],
            ],
            'partners' => [
                'title' => 'Đối tác tiêu biểu',
                'sort_order' => 50,
                'items' => [
                    ['title' => 'VIETBUILD'],
                    ['title' => 'MEGATECH'],
                    ['title' => 'THÀNH PHÁT GROUP'],
                    ['title' => 'NAM LONG INDUSTRIAL PARK'],
                    ['title' => 'GREENFEED'],
                    ['title' => 'AN PHÁT HOLDINGS'],
                ],
            ],
            'consultation_steps' => [
                'title' => 'Tư vấn giải pháp đúng nhu cầu',
                'sort_order' => 60,
                'items' => [
                    ['title' => '01', 'description' => 'Khảo sát tải trọng, tuyến vận hành và tần suất sử dụng.', 'metadata_json' => ['tone' => 'yellow']],
                    ['title' => '02', 'description' => 'Đề xuất xe, pin, phụ kiện và phương án bảo trì.', 'metadata_json' => ['tone' => 'blue']],
                    ['title' => '03', 'description' => 'Bàn giao, đào tạo vận hành và hỗ trợ kỹ thuật 24/7.', 'metadata_json' => ['tone' => 'green']],
                ],
            ],
        ];

        foreach ($sections as $key => $sectionData) {
            HomeSection::updateOrCreate(
                ['key' => $key],
                [
                    'title' => $sectionData['title'],
                    'subtitle' => $sectionData['subtitle'] ?? null,
                    'description' => $sectionData['description'] ?? null,
                    'is_enabled' => true,
                    'sort_order' => $sectionData['sort_order'],
                    'settings_json' => $sectionData['settings_json'] ?? null,
                ],
            );

            foreach ($sectionData['items'] as $index => $item) {
                HomeSectionItem::updateOrCreate(
                    [
                        'section_key' => $key,
                        'title' => $item['title'],
                    ],
                    [
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
