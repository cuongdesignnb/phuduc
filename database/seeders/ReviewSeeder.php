<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        if ($products->isEmpty()) return;

        $reviews = [
            ['name' => 'Nguyễn Văn An', 'phone' => '0909111000', 'email' => 'an.nv@gmail.com', 'rating' => 5, 'content' => 'Xe chạy rất êm, tải nặng vẫn khỏe. Đội ngũ kỹ thuật Phú Đức hỗ trợ rất nhanh và chuyên nghiệp. Sẽ tiếp tục đặt thêm cho nhà máy mới.'],
            ['name' => 'Trần Minh Đức', 'phone' => '0912222000', 'email' => 'duc.tm@yahoo.com', 'rating' => 5, 'content' => 'Đã dùng 6 tháng cho resort, khách hàng rất hài lòng. Pin trâu, sạc một đêm chạy cả ngày. Thiết kế đẹp, sang trọng.'],
            ['name' => 'Lê Thị Hồng', 'phone' => '0938333000', 'email' => 'hong.lt@hotmail.com', 'rating' => 4, 'content' => 'Xe tốt, giá hợp lý so với các hãng khác. Bảo hành 3 năm rất yên tâm. Chỉ mong thêm một vài tùy chọn màu sắc.'],
            ['name' => 'Phạm Quốc Hùng', 'phone' => '0977444000', 'email' => 'hung.pq@gmail.com', 'rating' => 5, 'content' => 'Chất lượng tuyệt vời, vượt xa kỳ vọng. Xe nâng điện PD-F15 hoạt động ổn định 10 tiếng liên tục. Rất hài lòng với dịch vụ hậu mãi.'],
            ['name' => 'Hoàng Minh Tú', 'phone' => '0901555000', 'email' => 'tu.hm@gmail.com', 'rating' => 4, 'content' => 'Mua 5 chiếc xe golf cho sân golf mới. Chạy mượt, khách chơi golf rất thích. Mong Phú Đức sớm có thêm phiên bản 6 chỗ.'],
            ['name' => 'Đặng Thanh Sơn', 'phone' => '0945666000', 'email' => 'son.dt@company.vn', 'rating' => 5, 'content' => 'Đã mua 20 xe cho KCN, tất cả đều hoạt động ổn định sau 1 năm. Chi phí vận hành giảm 65% so với xe xăng cũ. Đầu tư xứng đáng!'],
            ['name' => 'Ngô Thùy Linh', 'phone' => '0888777000', 'email' => 'linh.nt@hospital.vn', 'rating' => 5, 'content' => 'Bệnh viện chúng tôi dùng xe PD-P4 để chở bệnh nhân. Xe rất êm, không ồn, phù hợp môi trường bệnh viện. Nhân viên y tế ai cũng khen.'],
            ['name' => 'Bùi Văn Thành', 'phone' => '0933888000', 'email' => 'thanh.bv@logistics.vn', 'rating' => 4, 'content' => 'Xe kéo điện PD-TW5 kéo 5 moóc hàng dễ dàng. Tiết kiệm nhân lực đáng kể. Mong cải thiện thêm hệ thống phanh khi lên dốc.'],
            ['name' => 'Mai Thị Phương', 'phone' => '0922999000', 'email' => 'phuong.mt@resort.vn', 'rating' => 5, 'content' => 'Xe điện du lịch PD-P14 là niềm tự hào của resort chúng tôi. Du khách quốc tế ấn tượng với sự chuyên nghiệp và thân thiện môi trường.'],
            ['name' => 'Lý Đình Trung', 'phone' => '0911000111', 'email' => 'trung.ld@factory.com', 'rating' => 5, 'content' => 'So sánh với nhiều hãng khác, Phú Đức có giá tốt nhất mà chất lượng không thua kém hàng nhập khẩu. Dịch vụ 24/7 thực sự chứ không phải quảng cáo!'],
        ];

        foreach ($reviews as $review) {
            $product = $products->random();
            Review::create([
                'product_id' => $product->id,
                'customer_name' => $review['name'],
                'customer_phone' => $review['phone'],
                'customer_email' => $review['email'],
                'content' => $review['content'],
                'rating' => $review['rating'],
                'status' => 'approved',
            ]);
        }
    }
}
