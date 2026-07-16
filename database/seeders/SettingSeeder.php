<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ─── Site General ───
            ['key' => 'site.name', 'value' => 'Phú Đức - Xe Điện Công Nghiệp', 'type' => 'text'],
            ['key' => 'site.tagline', 'value' => 'Giải pháp xe điện hàng đầu Việt Nam', 'type' => 'text'],
            ['key' => 'site.description', 'value' => 'Phú Đức chuyên cung cấp xe điện công nghiệp, xe điện chở hàng, xe điện du lịch, xe điện sân golf chất lượng cao với bảo hành toàn diện.', 'type' => 'textarea'],
            ['key' => 'site.logo', 'value' => null, 'type' => 'image'],
            ['key' => 'site.favicon', 'value' => null, 'type' => 'image'],
            ['key' => 'site.email', 'value' => 'info@phuducev.vn', 'type' => 'text'],
            ['key' => 'site.phone', 'value' => '1900 636 518', 'type' => 'text'],
            ['key' => 'site.hotline', 'value' => '0909 123 456', 'type' => 'text'],
            ['key' => 'site.address', 'value' => '123 Đường Nguyễn Văn Linh, Quận 7, TP. Hồ Chí Minh', 'type' => 'text'],
            ['key' => 'site.working_hours', 'value' => 'Thứ 2 - Thứ 7: 8:00 - 17:30', 'type' => 'text'],
            ['key' => 'site.facebook', 'value' => 'https://facebook.com/phuducev', 'type' => 'text'],
            ['key' => 'site.zalo', 'value' => 'https://zalo.me/0909123456', 'type' => 'text'],
            ['key' => 'site.youtube', 'value' => 'https://youtube.com/@phuducev', 'type' => 'text'],
            ['key' => 'site.map_embed', 'value' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3920.0!2d106.7!3d10.7!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1', 'type' => 'text'],
            ['key' => 'site.primary_color', 'value' => '#ffd400', 'type' => 'color'],

            // ─── About Page ───
            ['key' => 'about.title', 'value' => 'Về Phú Đức', 'type' => 'text'],
            ['key' => 'about.content', 'value' => '<p>Công ty TNHH Phú Đức được thành lập năm 2014, chuyên cung cấp xe điện công nghiệp, xe điện chở hàng, xe điện du lịch phục vụ cho các khu công nghiệp, nhà máy, sân golf, resort và các đơn vị có nhu cầu vận chuyển nội bộ.</p><p>Với đội ngũ kỹ sư giàu kinh nghiệm và hệ thống showroom, xưởng bảo trì hiện đại, Phú Đức cam kết mang đến những sản phẩm chất lượng cao nhất, dịch vụ hậu mãi tốt nhất cho khách hàng.</p><p>Tầm nhìn: Trở thành nhà cung cấp xe điện công nghiệp số 1 Việt Nam, góp phần xây dựng nền công nghiệp xanh và bền vững.</p>', 'type' => 'textarea'],
            ['key' => 'about.image', 'value' => null, 'type' => 'image'],
            ['key' => 'about.mission', 'value' => 'Cung cấp giải pháp vận chuyển điện hóa tiên tiến, giúp doanh nghiệp tối ưu chi phí và bảo vệ môi trường.', 'type' => 'textarea'],
            ['key' => 'about.vision', 'value' => 'Trở thành nhà cung cấp xe điện công nghiệp hàng đầu Đông Nam Á vào năm 2030.', 'type' => 'textarea'],

            // ─── SEO Defaults ───
            ['key' => 'seo.default_title', 'value' => 'Phú Đức - Xe Điện Công Nghiệp Hàng Đầu Việt Nam', 'type' => 'text'],
            ['key' => 'seo.default_description', 'value' => 'Phú Đức chuyên phân phối xe điện công nghiệp, xe điện chở hàng, xe điện sân golf, xe điện du lịch. Bảo hành chính hãng, hỗ trợ kỹ thuật 24/7.', 'type' => 'textarea'],
            ['key' => 'seo.default_keywords', 'value' => 'xe điện công nghiệp, xe điện chở hàng, xe golf điện, xe điện du lịch, xe điện nhà xưởng, Phú Đức', 'type' => 'text'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'type' => $setting['type']]
            );
        }
    }
}
