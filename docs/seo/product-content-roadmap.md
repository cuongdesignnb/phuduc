# Phú Đức — Product Content SEO Roadmap

## Điểm xuất phát và cách chấm ưu tiên

Nguồn inventory là sitemap production `https://phuduc.com/sitemap.xml`, đọc ngày 26-08-2026 (ICT). Sitemap có **28 product URLs**. Không có dữ liệu GSC, search-volume hay doanh thu được sử dụng, vì không có quyền thao tác GSC và nhiệm vụ này không yêu cầu kết nối hệ thống đó.

Điểm ưu tiên là đánh giá biên tập 0–18, không phải ước lượng nhu cầu tìm kiếm:

| Thành phần | Điểm tối đa | Cách đánh giá từ crawl |
|---|---:|---|
| Khớp intent thương mại | 3 | URL có exact capacity/type/variant rõ ràng. |
| Khả năng phân biệt URL | 3 | Có ranh giới keyword khác sibling trong cùng cluster. |
| Fact nền đã có | 3 | Variant cung cấp capacity/nguồn/cấu hình có thể trích dẫn chính xác. |
| Độ sẵn sàng hình ảnh | 3 | Có gallery; không đánh giá chất lượng ảnh ngoài crawl. |
| Giá trị hub/internal link | 3 | Có thể tổ chức so sánh hoặc dẫn tới exact model. |
| Tác động sửa lỗi hiện hữu | 3 | Copy quá mỏng/boilerplate, cần sửa rõ rệt. |

- **P0:** 14–18 — ưu tiên chuẩn bị nội dung đầu tiên sau data gate.
- **P1:** 10–13 — chuẩn bị sau Wave 1/2.
- **P2:** 0–9 — chưa có đủ fact/hình ảnh để đưa vào sprint đầu.
- Điểm cao không thay thế fact sheet. Vì `specifications=[]` và `sku=null` trên 28/28 trang, **mọi URL vẫn mang trạng thái `DATA_REQUIRED`** trước khi nội dung mở rộng được xuất bản.

## Roadmap theo wave

### Wave 1 — Draft có kiểm soát: 5 sản phẩm P0

Mục tiêu là hoàn thiện draft, kiểm tra fact sheet và quyết định nguồn ảnh/variant; không tự publish và không ghi vào database.

| Thứ tự | URL | Product | Score | Lý do vào Wave 1 | Dependency bắt buộc |
|---:|---|---|---:|---|---|
| 1 | `/san-pham/cau-dien-thuy-luc-2-tan` | Cẩu điện thủy lực 2 tấn | 16 | Nhiều fact variant nhất: 2 tấn, 3 đốt, 3 m, 360°. | Catalogue/bản vẽ, biểu đồ tải, model motor/bình điện. |
| 2 | `/san-pham/can-cau-chan-nhen` | Cần cẩu chân nhện | 15 | Hub generic cho bốn mức tải 1/5/8/12 tấn. | Fact sheet và ảnh mapping cho từng mức tải. |
| 3 | `/san-pham/cau-truc-chay-dien-co-chan-di-chuyen` | Cẩu trục chạy điện có chân di chuyển | 15 | Ba mức tải và một nhóm keyword type rõ ràng. | Xác minh nghĩa của số đo cao/ngang 3 m, điện áp, chiều cao nâng. |
| 4 | `/san-pham/may-xuc-lat-4-banh-gau-0-4-m3` | Máy xúc lật 4 bánh gầu 0,4 m³ | 15 | Có comparison điện/diesel ở cùng dung tích gầu. | Datasheet riêng cho hai variant; mapping gallery 32 ảnh. |
| 5 | `/san-pham/xe-nang-dien` | Xe nâng điện | 15 | Hub cho 1/1,5/2,5/3,5 tấn, giúp tổ chức whole forklift cluster. | Datasheet từng mức tải, chiều cao nâng, battery/charger và ảnh mapping. |

**Deliverable Wave 1 hiện có:** 5 draft Markdown tại `docs/seo/drafts/products/`. Mỗi draft dùng fact đã crawl, nêu `DATA_MISSING`, không phát minh thông số và chưa sẵn sàng publish khi chưa qua data gate.

### Wave 2 — Mở rộng P0 còn lại: 6 sản phẩm

| URL | Product | Score | Công việc trọng tâm |
|---|---|---:|---|
| `/san-pham/cau-chan-nhen-chay-dien-1-5-tan` | Cẩu chân nhện chạy điện 1,5 tấn | 14 | Tách exact-model khỏi hub cần cẩu chân nhện. |
| `/san-pham/cau-thuy-luc-500-kg` | Cẩu thủy lực 500 kg | 14 | Xác minh motor tời và điều kiện tải 500 kg. |
| `/san-pham/may-xuc-lat-gau-0-7-m3` | Máy xúc lật gầu 0,7 m³ | 14 | Hoàn thiện cluster theo dung tích gầu. |
| `/san-pham/xe-nang-dien-0-8-tan` | Xe nâng điện 0,8 tấn | 14 | Exact-capacity page, link từ hub xe nâng điện. |
| `/san-pham/xe-rua-banh-xich-cho-hang-dieu-khien-tu-xa` | Xe rùa bánh xích chở hàng điều khiển từ xa | 14 | Xác minh remote/battery và tách ba tải trọng. |
| `/san-pham/xe-tai-banh-xich-cho-hang-1-8-tan` | Xe tải bánh xích chở hàng 1,8 tấn | 14 | Làm rõ “4 máy” và thông số diesel trước copy. |

### Wave 3 — Các trang P1: 16 sản phẩm

| Cluster | Product URLs | Điều kiện bắt đầu |
|---|---|---|
| Cẩu | Cẩu trục cố định xoay 360 độ chạy điện | Có dữ liệu đế cố định/tầm với/điện. |
| Thiết bị công trường | Máy bơm bê tông chạy dầu; Máy phun vữa tường chạy điện; Máy cắt điều khiển từ xa; Máy xúc lật 3 bánh gầu 0,2 m³ | Datasheet ứng dụng, năng lượng, kích thước và safety. |
| Phụ trợ/hạ tầng | Điều hòa 24V cho xe tải cắt nóc; Nhà container | Tương thích/lắp đặt hoặc kích thước/layout được duyệt. |
| Xếp dỡ | Máy gắp hàng khí nén 100 kg; Motor tời; Xe nâng pallet 2 tấn; Xe nâng tay | Điều kiện tải, thành phần, thông số kích thước được xác nhận. |
| Vận chuyển | Xe chở hàng 3 bánh 1 tấn; Xe chở hàng chạy điện; Xe nâng kết hợp chở hàng 1,5 tấn | Cấu hình nguồn, kích thước và khả năng chức năng được xác nhận. |
| Xe nâng | Xe nâng dầu diesel 3 tấn; Xe nâng điện 500 kg 48V | Datasheet mast/load centre/nguồn năng lượng. |

### Wave 4 — P2: 1 sản phẩm

| URL | Product | Score | Lý do giữ lại | Gate để đưa lên Wave 3 |
|---|---|---:|---|---|
| `/san-pham/may-phat-dien-nap-cho-xe-tai-chay-xang-7l` | Máy phát điện nạp cho xe tải chạy xăng 7L | 8 | Không có gallery; các claim tự động/Bluetooth chưa có tài liệu kỹ thuật trong trang. | Ảnh hero, manual/specsheet, điện áp/đầu ra, ý nghĩa 7L và xác minh tính năng. |

## Tổng hợp định lượng

```text
PRODUCT_COUNT=28
P0_COUNT=11
P1_COUNT=16
P2_COUNT=1
DATA_REQUIRED_COUNT=28
WAVE_1_DRAFT_COUNT=5
WAVE_1_PRODUCTS=cau-dien-thuy-luc-2-tan,can-cau-chan-nhen,cau-truc-chay-dien-co-chan-di-chuyen,may-xuc-lat-4-banh-gau-0-4-m3,xe-nang-dien
```

## Backlog bài informational và quy tắc dẫn link

Các ý tưởng sau là backlog, không phải bài đã viết. Chỉ triển khai sau khi factual data của product destination đã được duyệt; bài viết không được đổi owner keyword của product page.

| # | Ý tưởng bài viết informational | Intent | Product page được phép dẫn link | Quy tắc tránh cannibalization |
|---:|---|---|---|---|
| 1 | Cách lập checklist thông số trước khi chọn cẩu theo tải trọng | Informational | Cần cẩu chân nhện; cẩu điện thủy lực 2 tấn; cẩu thủy lực 500 kg | Không tối ưu H1 cho một model/variant cụ thể. |
| 2 | Cách đọc tải trọng, tầm với và điều kiện tải trong catalogue cẩu | Informational | Cẩu điện thủy lực 2 tấn; cẩu trục có chân di chuyển | Chỉ dùng bảng minh họa sau khi catalogue được duyệt. |
| 3 | Những dữ liệu cần so sánh khi chọn máy xúc lật theo dung tích gầu | Informational | Máy xúc lật 0,2 / 0,4 / 0,7 m³ | Product pages giữ exact-capacity keyword. |
| 4 | Checklist kiểm tra pin, bộ sạc và chiều cao nâng của xe nâng điện | Informational | Xe nâng điện; 0,8 tấn; 500 kg 48V | Không ghi thông số của Phú Đức nếu chưa có fact sheet. |
| 5 | Phân biệt dữ liệu cần có trước khi chọn xe nâng pallet và xe nâng tay | Informational | Xe nâng pallet 2 tấn; Xe nâng tay | Không dùng “mua xe nâng pallet 2 tấn” làm title bài. |
| 6 | Checklist thông số kỹ thuật cho xe chở hàng chạy điện | Informational | Xe chở hàng chạy điện; xe 3 bánh 1 tấn; xe rùa bánh xích | Không khẳng định phạm vi/quãng đường khi không có source. |
| 7 | Những câu hỏi cần hỏi trước khi chọn xe bánh xích chở hàng | Informational | Xe rùa bánh xích; xe tải bánh xích 1,8 tấn | Không tự đưa số độ dốc, tốc độ hay tải thực tế. |
| 8 | Cách kiểm tra khả năng tương thích của điều hòa 24V trên xe tải | Informational | Điều hòa 24V cho xe tải cắt nóc | Không khẳng định tương thích với model xe nào. |
| 9 | Thông tin cần chuẩn bị trước khi yêu cầu báo giá nhà container | Informational | Nhà container | Không đưa layout/kích thước không có nguồn. |
| 10 | Vì sao cần xác nhận đơn vị tải và điều kiện đo của motor tời | Informational | Motor tời | Không gọi lbs là tải nâng nếu catalogue chưa xác nhận. |
| 11 | Checklist an toàn trước khi đánh giá máy gắp hàng khí nén | Informational | Máy gắp hàng khí nén 100 kg | Không đưa áp suất/loại đầu gắp không có dữ liệu. |
| 12 | Cách đối chiếu catalogue máy phát nạp xe tải trước khi mua | Informational | Máy phát điện nạp xe tải chạy xăng 7L | Chỉ xuất bản khi manual và ảnh sản phẩm được duyệt. |

## Data gate và QA trước publish

1. **Data ownership:** mỗi URL/variant có link tới catalogue/manual/xác nhận nhà cung cấp; không dùng memory, ảnh tương tự hoặc sản phẩm khác làm nguồn.
2. **Copy QA:** tất cả số liệu xuất hiện trong visible copy, meta, FAQ, table và schema phải match fact sheet. Fact chưa có giữ trong `DATA_MISSING`, không chuyển thành câu khẳng định.
3. **On-page QA:** title/H1/meta duy nhất theo Product Content Map; canonical self, một H1, headings theo logic lựa chọn, không có note nội bộ.
4. **Image QA:** file ảnh được đối chiếu variant; alt mô tả đúng ảnh, ngắn gọn và không nhồi keyword.
5. **Link QA:** links chỉ dẫn tới sibling có quan hệ lựa chọn thật; anchor phản ánh exact owner URL.
6. **SEO technical QA:** kiểm tra rendered HTML/schema/canonical bằng staging trước deploy. Không thay đổi production trong sprint tài liệu này.
