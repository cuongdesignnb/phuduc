<?php

namespace App\Services\Admin\Content;

final class AdminSettingRegistry
{
    /** @return array<string, array<string, mixed>> */
    public static function all(): array
    {
        $definitions = [
            'site.name' => ['label' => 'Tên website', 'description' => 'Tên thương hiệu hiển thị trên website.', 'group' => 'site', 'type' => 'text', 'default' => 'PhuDuc', 'max' => 255],
            'site.tagline' => ['label' => 'Khẩu hiệu', 'description' => 'Dòng giới thiệu ngắn của thương hiệu.', 'group' => 'site', 'type' => 'text', 'default' => '', 'max' => 255],
            'site.description' => ['label' => 'Mô tả website', 'description' => 'Mô tả mặc định cho website.', 'group' => 'site', 'type' => 'textarea', 'default' => '', 'max' => 2000],
            'site.logo' => ['label' => 'Logo', 'description' => 'Logo chính được chọn từ Media Library.', 'group' => 'site', 'type' => 'image', 'default' => null, 'max' => null],
            'site.favicon' => ['label' => 'Favicon', 'description' => 'Biểu tượng trình duyệt được chọn từ Media Library.', 'group' => 'site', 'type' => 'image', 'default' => null, 'max' => null],
            'site.og_image' => ['label' => 'Ảnh chia sẻ mạng xã hội', 'description' => 'Ảnh Open Graph mặc định cho liên kết chia sẻ.', 'group' => 'site', 'type' => 'image', 'default' => null, 'max' => null],
            'site.email' => ['label' => 'Email', 'description' => 'Email liên hệ công khai.', 'group' => 'site', 'type' => 'text', 'default' => '', 'max' => 255],
            'site.phone' => ['label' => 'Số điện thoại', 'description' => 'Số điện thoại liên hệ.', 'group' => 'site', 'type' => 'text', 'default' => '', 'max' => 50],
            'site.hotline' => ['label' => 'Hotline', 'description' => 'Hotline hỗ trợ khách hàng.', 'group' => 'site', 'type' => 'text', 'default' => '', 'max' => 50],
            'site.address' => ['label' => 'Địa chỉ', 'description' => 'Địa chỉ doanh nghiệp.', 'group' => 'site', 'type' => 'text', 'default' => '', 'max' => 500],
            'site.working_hours' => ['label' => 'Giờ làm việc', 'description' => 'Khung giờ phục vụ khách hàng.', 'group' => 'site', 'type' => 'text', 'default' => '', 'max' => 255],
            'site.facebook' => ['label' => 'Facebook', 'description' => 'Liên kết Facebook hợp lệ.', 'group' => 'site', 'type' => 'text', 'default' => '', 'max' => 500],
            'site.zalo' => ['label' => 'Zalo', 'description' => 'Liên kết Zalo hợp lệ.', 'group' => 'site', 'type' => 'text', 'default' => '', 'max' => 500],
            'site.youtube' => ['label' => 'YouTube', 'description' => 'Liên kết YouTube hợp lệ.', 'group' => 'site', 'type' => 'text', 'default' => '', 'max' => 500],
            'site.map_embed' => ['label' => 'Bản đồ nhúng', 'description' => 'URL bản đồ được phép nhúng.', 'group' => 'site', 'type' => 'text', 'default' => '', 'max' => 1000],
            'site.primary_color' => ['label' => 'Màu chủ đạo', 'description' => 'Màu hex sáu ký tự cho giao diện.', 'group' => 'site', 'type' => 'color', 'default' => '#ffd400', 'max' => 7],
            'about.title' => ['label' => 'Tiêu đề giới thiệu', 'description' => 'Tiêu đề trang giới thiệu.', 'group' => 'about', 'type' => 'text', 'default' => 'Về PhuDuc', 'max' => 255],
            'about.content' => ['label' => 'Nội dung giới thiệu', 'description' => 'Nội dung giới thiệu doanh nghiệp.', 'group' => 'about', 'type' => 'textarea', 'default' => '', 'max' => 50000],
            'about.image' => ['label' => 'Ảnh giới thiệu', 'description' => 'Ảnh trang giới thiệu từ Media Library.', 'group' => 'about', 'type' => 'image', 'default' => null, 'max' => null],
            'about.mission' => ['label' => 'Sứ mệnh', 'description' => 'Sứ mệnh doanh nghiệp.', 'group' => 'about', 'type' => 'textarea', 'default' => '', 'max' => 5000],
            'about.vision' => ['label' => 'Tầm nhìn', 'description' => 'Tầm nhìn doanh nghiệp.', 'group' => 'about', 'type' => 'textarea', 'default' => '', 'max' => 5000],
            'seo.default_title' => ['label' => 'Tiêu đề SEO mặc định', 'description' => 'Tiêu đề mặc định cho trang chưa có SEO riêng.', 'group' => 'seo', 'type' => 'text', 'default' => 'PhuDuc', 'max' => 255],
            'seo.default_description' => ['label' => 'Mô tả SEO mặc định', 'description' => 'Mô tả mặc định cho công cụ tìm kiếm.', 'group' => 'seo', 'type' => 'textarea', 'default' => '', 'max' => 500],
            'seo.default_keywords' => ['label' => 'Từ khóa SEO', 'description' => 'Danh sách từ khóa SEO cách nhau bằng dấu phẩy.', 'group' => 'seo', 'type' => 'text', 'default' => '', 'max' => 1000],
            'font.heading' => ['label' => 'Font tiêu đề', 'description' => 'Font dùng cho tiêu đề giao diện.', 'group' => 'appearance', 'type' => 'font', 'default' => 'Rajdhani', 'max' => 100],
            'font.body' => ['label' => 'Font nội dung', 'description' => 'Font dùng cho nội dung giao diện.', 'group' => 'appearance', 'type' => 'font', 'default' => 'Inter', 'max' => 100],
        ];

        return collect($definitions)->mapWithKeys(fn (array $definition, string $key) => [$key => ['key' => $key, 'options' => [], ...$definition]])->all();
    }

    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }
}
