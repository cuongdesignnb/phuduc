<?php

namespace Database\Seeders;

use App\Models\PostCategory;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Categories ───
        $tintuc = PostCategory::create(['name' => 'Tin tức', 'slug' => 'tin-tuc', 'description' => 'Tin tức ngành xe điện công nghiệp']);
        $kienth = PostCategory::create(['name' => 'Kiến thức', 'slug' => 'kien-thuc', 'description' => 'Hướng dẫn, mẹo sử dụng xe điện']);
        $sukien = PostCategory::create(['name' => 'Sự kiện', 'slug' => 'su-kien', 'description' => 'Sự kiện, triển lãm xe điện']);
        $review = PostCategory::create(['name' => 'Đánh giá', 'slug' => 'danh-gia', 'description' => 'Review, so sánh các dòng xe']);

        // ─── Posts ───
        $posts = [
            [
                'category' => $tintuc,
                'title' => 'Xu hướng xe điện công nghiệp 2025: Tương lai của vận chuyển nội bộ',
                'summary' => 'Năm 2025 đánh dấu bước ngoặt phát triển xe điện công nghiệp tại Việt Nam với nhiều chính sách hỗ trợ từ Chính phủ và nhu cầu tăng mạnh từ các khu công nghiệp.',
                'content' => '<h2>Xe điện công nghiệp đang thay đổi ngành vận tải</h2><p>Theo báo cáo mới nhất từ Hiệp hội Công nghiệp Việt Nam, thị trường xe điện công nghiệp đang tăng trưởng 35% mỗi năm. Các doanh nghiệp ngày càng nhận thức được lợi ích của việc chuyển đổi sang phương tiện vận chuyển nội bộ chạy điện.</p><h3>Lợi ích kinh tế</h3><p>Chi phí vận hành xe điện thấp hơn 60% so với xe chạy xăng/dầu truyền thống. Với hệ thống ắc quy lithium-ion thế hệ mới, tuổi thọ pin lên đến 5-7 năm, giảm thiểu chi phí thay thế.</p><h3>Bảo vệ môi trường</h3><p>Không khí thải trực tiếp, giảm ô nhiễm tiếng ồn đáng kể - đặc biệt quan trọng trong môi trường nhà xưởng kín. Đây là yếu tố then chốt giúp doanh nghiệp đạt các chứng nhận ISO về môi trường.</p><h3>Chính sách hỗ trợ</h3><p>Chính phủ đang triển khai nhiều ưu đãi thuế và hỗ trợ lãi suất cho doanh nghiệp chuyển đổi sang phương tiện điện, tạo điều kiện thuận lợi cho xu hướng này phát triển mạnh mẽ.</p>',
                'status' => 'published',
            ],
            [
                'category' => $kienth,
                'title' => '5 cách bảo dưỡng ắc quy xe điện kéo dài tuổi thọ gấp đôi',
                'summary' => 'Hướng dẫn chi tiết cách bảo dưỡng, sạc và bảo quản ắc quy xe điện công nghiệp đúng cách để tối ưu hiệu suất và kéo dài tuổi thọ.',
                'content' => '<h2>Bảo dưỡng ắc quy đúng cách - Tiết kiệm hàng trăm triệu</h2><p>Ắc quy là "trái tim" của xe điện và chiếm 30-40% giá trị xe. Bảo dưỡng đúng cách giúp kéo dài tuổi thọ đáng kể.</p><h3>1. Sạc đúng quy trình</h3><p>Luôn sạc đầy ắc quy sau mỗi ca làm việc. Không để ắc quy cạn kiệt hoàn toàn trước khi sạc. Sử dụng bộ sạc chính hãng với chế độ tự ngắt.</p><h3>2. Kiểm tra mực nước cất định kỳ</h3><p>Đối với ắc quy axit-chì, kiểm tra và bổ sung nước cất mỗi 2 tuần. Mực nước phải ngập các bản cực ít nhất 1cm.</p><h3>3. Vệ sinh đầu cực thường xuyên</h3><p>Dùng dung dịch baking soda pha loãng vệ sinh các đầu cực mỗi tháng, bôi mỡ chống oxy hóa để đảm bảo tiếp xúc tốt.</p><h3>4. Bảo quản nơi thoáng mát</h3><p>Nhiệt độ cao làm giảm tuổi thọ ắc quy nhanh chóng. Để xe nơi có mái che, thoáng khí, tránh ánh nắng trực tiếp.</p><h3>5. Cân bằng cụm ắc quy định kỳ</h3><p>Mỗi 3 tháng nên thực hiện sạc cân bằng (equalize charge) để đồng đều điện áp giữa các cell, ngăn ngừa hiện tượng phân tầng axit.</p>',
                'status' => 'published',
            ],
            [
                'category' => $sukien,
                'title' => 'Phú Đức tham gia Triển lãm Công nghiệp Quốc tế Vietnam EV Expo 2025',
                'summary' => 'Phú Đức vinh dự là nhà tài trợ Vàng và trưng bày 8 mẫu xe điện mới nhất tại Vietnam EV Expo 2025 diễn ra tại SECC Quận 7.',
                'content' => '<h2>Vietnam EV Expo 2025 - Triển lãm xe điện lớn nhất Việt Nam</h2><p>Từ ngày 15-18/05/2025, Phú Đức vinh dự là nhà tài trợ cấp Vàng tại Triển lãm Xe điện & Năng lượng xanh Quốc tế (Vietnam EV Expo 2025) diễn ra tại Trung tâm Hội chợ và Triển lãm Sài Gòn (SECC), Quận 7, TP. HCM.</p><h3>Highlight sự kiện</h3><ul><li>Ra mắt 3 mẫu xe điện hoàn toàn mới dành cho khu công nghiệp</li><li>Demo trực tiếp xe điện chở hàng tải trọng 2 tấn</li><li>Ưu đãi đặc biệt lên đến 15% cho khách đặt hàng tại sự kiện</li><li>Tư vấn giải pháp miễn phí từ đội ngũ kỹ sư chuyên gia</li></ul><p>Hãy đến gian hàng A15 của chúng tôi để trải nghiệm và nhận ưu đãi!</p>',
                'status' => 'published',
            ],
            [
                'category' => $review,
                'title' => 'So sánh xe điện chở hàng PD-T2000 vs PD-T3000: Nên chọn loại nào?',
                'summary' => 'Phân tích chi tiết ưu nhược điểm giữa 2 dòng xe tải điện bán chạy nhất để giúp bạn lựa chọn phù hợp với nhu cầu.',
                'content' => '<h2>PD-T2000 vs PD-T3000: Cuộc đua giữa hai "chiến mã"</h2><p>Cả hai dòng xe đều là sản phẩm chủ lực của Phú Đức, nhưng phục vụ nhu cầu khác nhau.</p><h3>PD-T2000 - Linh hoạt, đa năng</h3><p>Tải trọng: 2 tấn | Tốc độ tối đa: 30km/h | Phạm vi: 80km/lần sạc. Phù hợp cho kho bãi vừa và nhỏ, vận chuyển trong khu vực sản xuất.</p><h3>PD-T3000 - Mạnh mẽ, bền bỉ</h3><p>Tải trọng: 3 tấn | Tốc độ tối đa: 25km/h | Phạm vi: 60km/lần sạc. Thiết kế cho công việc nặng, địa hình phức tạp, khu công nghiệp lớn.</p><h3>Kết luận</h3><p>Nếu cần sự linh hoạt và phạm vi hoạt động rộng, PD-T2000 là lựa chọn tối ưu. Nếu ưu tiên sức tải và độ bền trong điều kiện khắc nghiệt, PD-T3000 sẽ đáp ứng tốt hơn.</p>',
                'status' => 'published',
            ],
            [
                'category' => $tintuc,
                'title' => 'Phú Đức ký kết hợp tác chiến lược với Tập đoàn Samsung tại Bắc Ninh',
                'summary' => 'Phú Đức trở thành nhà cung cấp xe điện nội bộ chính thức cho nhà máy Samsung Electronics Việt Nam tại Bắc Ninh.',
                'content' => '<h2>Hợp tác chiến lược Phú Đức - Samsung</h2><p>Ngày 01/03/2025, Phú Đức đã ký kết thỏa thuận hợp tác chiến lược 5 năm với Samsung Electronics Vietnam tại Bắc Ninh, cung cấp toàn bộ giải pháp xe điện vận chuyển nội bộ cho nhà máy.</p><p>Theo thỏa thuận, Phú Đức sẽ cung cấp 50 xe điện chở hàng PD-T3000 và 30 xe điện chở người PD-P14 trong giai đoạn đầu, cùng với dịch vụ bảo trì, bảo dưỡng toàn diện.</p><p>Đây là minh chứng cho chất lượng sản phẩm và dịch vụ của Phú Đức, được các tập đoàn quốc tế hàng đầu tin tưởng lựa chọn.</p>',
                'status' => 'published',
            ],
            [
                'category' => $kienth,
                'title' => 'Hướng dẫn lựa chọn xe điện phù hợp cho từng loại hình doanh nghiệp',
                'summary' => 'Mỗi ngành nghề có nhu cầu khác nhau. Bài viết giúp bạn xác định chính xác loại xe điện cần thiết cho doanh nghiệp mình.',
                'content' => '<h2>Chọn xe điện đúng cách - Tiết kiệm chi phí, tối ưu hiệu quả</h2><h3>Khu công nghiệp / Nhà máy</h3><p>Ưu tiên xe tải điện tải trọng 1-3 tấn, phanh tái sinh điện năng, cabin kín. Đề xuất: PD-T2000, PD-T3000.</p><h3>Sân Golf / Resort</h3><p>Xe điện chở khách 4-14 chỗ, thiết kế sang trọng, êm ái, tốc độ thấp an toàn. Đề xuất: PD-G4, PD-P8, PD-P14.</p><h3>Bệnh viện / Trường học</h3><p>Xe điện chở người cỡ nhỏ 4-8 chỗ, tiếng ồn thấp, dễ vận hành. Đề xuất: PD-P4, PD-P8.</p><h3>Kho bãi / Logistics</h3><p>Ưu tiên xe nâng điện và xe kéo điện, khả năng hoạt động liên tục 8-10 giờ. Đề xuất: PD-F15, PD-TW5.</p>',
                'status' => 'published',
            ],
            [
                'category' => $review,
                'title' => 'Review xe điện du lịch PD-P14: 14 chỗ ngồi, vận hành êm ái',
                'summary' => 'Đánh giá chi tiết xe điện du lịch PD-P14 sau 6 tháng vận hành thực tế tại một resort 5 sao tại Phú Quốc.',
                'content' => '<h2>PD-P14: Đẳng cấp di chuyển trong resort</h2><p>Sau 6 tháng vận hành liên tục tại Vinpearl Phú Quốc với khối lượng trung bình 120 lượt/ngày, xe điện PD-P14 đã chứng minh được sức bền và sự tin cậy.</p><h3>Ưu điểm nổi bật</h3><ul><li>Khung gầm hợp kim nhôm nhẹ nhưng chắc chắn</li><li>Pin lithium 72V/150Ah cho phạm vi 100km/lần sạc</li><li>Hệ thống treo độc lập 4 bánh, êm ái trên mọi địa hình</li><li>Thiết kế mái che thoáng, ghế simili cao cấp</li></ul><h3>Điểm cần cải thiện</h3><ul><li>Hệ thống đèn LED có thể nâng cấp sáng hơn cho vận hành ban đêm</li><li>Thêm cổng sạc USB cho hành khách</li></ul><h3>Kết luận</h3><p>PD-P14 xứng đáng 4.5/5 sao cho phân khúc xe điện du lịch cao cấp.</p>',
                'status' => 'published',
            ],
            [
                'category' => $tintuc,
                'title' => 'Thị trường xe điện Việt Nam dự kiến đạt quy mô 2 tỷ USD vào 2028',
                'summary' => 'Báo cáo mới từ Research & Markets cho thấy tiềm năng to lớn của thị trường xe điện tại Việt Nam trong 3 năm tới.',
                'content' => '<h2>Tiềm năng khổng lồ của thị trường xe điện Việt Nam</h2><p>Theo báo cáo mới nhất từ Research & Markets, thị trường xe điện Việt Nam (bao gồm xe điện công nghiệp, xe điện cá nhân và xe buýt điện) dự kiến đạt quy mô 2 tỷ USD vào năm 2028, tăng trưởng bình quân 42% mỗi năm.</p><p>Các yếu tố thúc đẩy bao gồm: chính sách ưu đãi thuế, cam kết Net Zero 2050 của Việt Nam, giá pin lithium giảm mạnh, và nhu cầu tối ưu chi phí vận hành từ các doanh nghiệp.</p><p>Phú Đức đang đầu tư mạnh mẽ vào R&D và mở rộng mạng lưới phân phối để đón đầu làn sóng tăng trưởng này.</p>',
                'status' => 'published',
            ],
        ];

        foreach ($posts as $data) {
            Post::create([
                'post_category_id' => $data['category']->id,
                'title' => $data['title'],
                'slug' => Str::slug($data['title']),
                'summary' => $data['summary'],
                'content' => $data['content'],
                'featured_image' => null,
                'status' => $data['status'],
            ]);
        }
    }
}
