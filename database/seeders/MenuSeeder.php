<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Header Menu ───
        $header = Menu::create(['name' => 'Menu chính', 'location' => 'header']);

        $headerItems = [
            ['title' => 'Trang chủ', 'url' => '/', 'sort_order' => 1],
            ['title' => 'Sản phẩm', 'url' => '/san-pham', 'sort_order' => 2],
            ['title' => 'Tin tức', 'url' => '/tin-tuc', 'sort_order' => 3],
            ['title' => 'Tra cứu bảo hành', 'url' => '/tra-cuu-bao-hanh', 'sort_order' => 4],
            ['title' => 'Tra cứu đơn hàng', 'url' => '/tra-cuu-don-hang', 'sort_order' => 5],
            ['title' => 'Về chúng tôi', 'url' => '/gioi-thieu', 'sort_order' => 6],
        ];

        foreach ($headerItems as $item) {
            MenuItem::create(array_merge($item, ['menu_id' => $header->id]));
        }

        // ─── Footer Menu ───
        $footer = Menu::create(['name' => 'Menu footer', 'location' => 'footer']);

        $support = MenuItem::create(['menu_id' => $footer->id, 'title' => 'Hỗ trợ khách hàng', 'url' => null, 'sort_order' => 1]);
        $supportItems = [
            ['title' => 'Chính sách bảo hành', 'url' => '/chinh-sach-bao-hanh', 'sort_order' => 1],
            ['title' => 'Hướng dẫn mua hàng', 'url' => '/huong-dan-mua-hang', 'sort_order' => 2],
            ['title' => 'Vận chuyển & Giao nhận', 'url' => '/van-chuyen', 'sort_order' => 3],
            ['title' => 'Câu hỏi thường gặp', 'url' => '/faq', 'sort_order' => 4],
        ];
        foreach ($supportItems as $item) {
            MenuItem::create(array_merge($item, ['menu_id' => $footer->id, 'parent_id' => $support->id]));
        }

        $about = MenuItem::create(['menu_id' => $footer->id, 'title' => 'Về Phú Đức', 'url' => null, 'sort_order' => 2]);
        $aboutItems = [
            ['title' => 'Giới thiệu công ty', 'url' => '/gioi-thieu', 'sort_order' => 1],
            ['title' => 'Tuyển dụng', 'url' => '/tuyen-dung', 'sort_order' => 2],
            ['title' => 'Liên hệ', 'url' => '/lien-he', 'sort_order' => 3],
        ];
        foreach ($aboutItems as $item) {
            MenuItem::create(array_merge($item, ['menu_id' => $footer->id, 'parent_id' => $about->id]));
        }
    }
}
