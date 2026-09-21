# Draft — Cần cẩu chân nhện

- **URL:** `https://phuduc.com/san-pham/can-cau-chan-nhen`
- **Cluster / priority:** Cẩu và nâng hạ / P0 (15/18)
- **Status:** `DRAFT_READY_WITH_DATA_GAPS — DO NOT PUBLISH WITHOUT FACT SHEET`
- **Target length:** Dừng trước mức 600 từ để tránh tự tạo thông số kỹ thuật; bản mở rộng chỉ được viết sau khi có fact sheet riêng cho từng mức tải.

## FACTS USED

Snapshot production ngày 26-08-2026 hiển thị 4 variant của trang này:

- 1 tấn; 5 tấn; 8 tấn; 12 tấn.
- Trang có canonical tự trỏ, `robots: index, follow` và gallery 12 ảnh.
- Mô tả công khai hiện chỉ là tên sản phẩm; không có `specifications` có cấu trúc.

## DATA MISSING

- Model/SKU, nguồn năng lượng, kích thước, khối lượng, tầm với/chiều cao, điều kiện tải, phương thức vận hành và phụ kiện cho từng variant.
- Mapping 12 ảnh theo mức tải; bảo hành và khả năng có hàng theo từng cấu hình.

Không gọi tất cả variant là chạy điện, không nêu công trường/địa hình, và không dùng fact của trang cẩu chân nhện 1,5 tấn cho trang hub này.

## Metadata đề xuất

- **Meta title:** Cần cẩu chân nhện 1–12 tấn | Các phiên bản | Phú Đức
- **Meta description:** Cần cẩu chân nhện hiện hiển thị các phiên bản 1, 5, 8 và 12 tấn tại Phú Đức. Xem cấu hình đã xác nhận và hình ảnh sản phẩm.
- **H1:** Cần cẩu chân nhện

## H2/H3 outline

1. H2: Các phiên bản cần cẩu chân nhện đang hiển thị
2. H2: So sánh theo tải trọng
3. H2: Dữ liệu cần xác nhận cho từng cấu hình
4. H2: Hình ảnh sản phẩm
5. H2: Liên hệ nhận catalogue

## Draft body copy

### Các phiên bản cần cẩu chân nhện đang hiển thị

Trang **Cần cẩu chân nhện** là điểm bắt đầu để đối chiếu các mức tải hiện có trên cùng nhóm sản phẩm. Listing hiện hiển thị bốn lựa chọn: 1 tấn, 5 tấn, 8 tấn và 12 tấn. Vì đây là các variant riêng, thông tin của mỗi mức tải cần được kiểm tra bằng tài liệu của chính variant đó trước khi quyết định.

### So sánh theo tải trọng

Mức tải là fact duy nhất có thể so sánh trực tiếp từ dữ liệu công khai hiện tại. Trang này nên dẫn người xem tới catalogue hoặc tư vấn kỹ thuật để làm rõ các yếu tố còn thiếu như tầm với, chiều cao, nguồn năng lượng, kích thước và điều kiện tải. Không nên dùng mức tải để suy ra các đặc điểm khác.

Nếu cần cấu hình được ghi rõ là chạy điện 1,5 tấn, hãy xem trang riêng cho **Cẩu chân nhện chạy điện 1,5 tấn**. Trang đó có keyword ownership khác và không phải dữ liệu thay thế cho bốn variant trên trang này.

### Dữ liệu cần xác nhận cho từng cấu hình

Trước khi báo giá hoặc xuất bản mô tả đầy đủ, Phú Đức cần có fact sheet cho từng variant. Fact sheet tối thiểu cần xác định model, tải trọng trong điều kiện đo cụ thể, kích thước, khối lượng, nguồn năng lượng, tầm với/chiều cao, phụ kiện và ảnh đúng cấu hình.

### Hình ảnh sản phẩm

Gallery hiện có 12 ảnh. Chỉ nên dùng caption mô tả chi tiết khi ảnh đã được xác nhận thuộc đúng variant; còn lại alt nên giữ ở mức tên sản phẩm trung tính.

### Liên hệ nhận catalogue

Liên hệ Phú Đức để nhận catalogue và xác nhận variant cần cẩu chân nhện phù hợp với yêu cầu tải trọng trước khi đặt hàng.

## Internal links đề xuất

- [Cẩu chân nhện chạy điện 1,5 tấn](https://phuduc.com/san-pham/cau-chan-nhen-chay-dien-1-5-tan) — trang exact-model riêng.
- [Cẩu điện thủy lực 2 tấn](https://phuduc.com/san-pham/cau-dien-thuy-luc-2-tan) — cấu hình 2 tấn có dữ liệu variant khác.
- [Cẩu trục chạy điện có chân di chuyển](https://phuduc.com/san-pham/cau-truc-chay-dien-co-chan-di-chuyen) — so sánh với một loại cẩu trục khác, không dùng chung spec.

## CTA

**Yêu cầu catalogue cho đúng variant 1, 5, 8 hoặc 12 tấn để xác nhận thông số trước khi chọn.**

## Image alt plan

- Hero: `Cần cẩu chân nhện – ảnh sản phẩm`.
- Không chèn mức tải vào alt trừ khi hệ thống media có mapping ảnh–variant đã được kiểm duyệt.
- Không thêm các từ về năng lượng, địa hình hoặc tầm với khi chưa có fact sheet.

## QA checklist

- [x] Bốn mức tải khớp variant production đã crawl.
- [x] Hub generic được tách rõ khỏi URL cẩu chân nhện chạy điện 1,5 tấn.
- [x] Không có claim về nguồn năng lượng, tầm với, điều kiện sử dụng hoặc giá.
- [x] Có 3 internal links có mục đích so sánh rõ ràng.
- [x] `DATA_MISSING` được công khai trước khi biên tập mở rộng.
