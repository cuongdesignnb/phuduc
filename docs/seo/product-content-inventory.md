# Phú Đức — Product Content SEO Inventory

## Phạm vi và phương pháp

- **Nguồn inventory:** sitemap production đọc ở chế độ chỉ-đọc: `https://phuduc.com/sitemap.xml`.
- **Thời điểm kiểm tra:** 26-08-2026 (ICT).
- **Kết quả sitemap:** 32 URL tuyệt đối, gồm 4 URL core (`/`, `/san-pham`, `/tin-tuc`, `/gioi-thieu`) và **28 URL sản phẩm**.
- **Trạng thái crawl:** 28/28 URL sản phẩm trả HTTP 200, canonical tự trỏ `https://phuduc.com/san-pham/{slug}` và `robots: index, follow`.
- **Phạm vi dữ liệu đã kiểm tra:** title, meta description, canonical, robots, H1/HTML được render, JSON page data, variants, hình ảnh, liên kết liên quan và các trường dữ liệu mà source code storefront sử dụng.

Đây là snapshot biên tập, không phải thay đổi production. Không có SSH, deploy, thay đổi database sản phẩm hay thao tác GSC nào được thực hiện.

## Kết luận inventory

`PRODUCT_COUNT=28`

- Toàn bộ 28 trang có title riêng, canonical hợp lệ và có thể index.
- Mô tả thân trang đều rất ngắn (thường chỉ lặp lại tên sản phẩm); không có trang nào đang có một phần mô tả lựa chọn/ứng dụng đủ chiều sâu.
- Meta description hiện theo công thức tên sản phẩm + phiên bản/giá, nên tuy không trùng nguyên văn nhưng vẫn là boilerplate và cần biên tập lại theo từng intent.
- Trường `specifications` storefront trả về mảng rỗng trên cả 28 trang; dữ liệu hiện có chủ yếu là tên variant, giá và đôi khi một ghi chú variant. Vì vậy mọi trang đều cần xác minh dữ liệu nguồn trước khi xuất bản nội dung mở rộng.
- Liên kết “sản phẩm liên quan” hiện lặp lại một tập sản phẩm khá chung, chưa thể hiện hub/cluster theo ý định tìm kiếm.

Quy ước trong bảng:

- **THIN**: có mô tả nhưng không đủ thông tin mua hàng/đánh giá.
- **PARTIAL (variant-only)**: có một hoặc nhiều variant làm fact nền, nhưng không có bảng thông số có cấu trúc.
- **DATA_REQUIRED**: không viết/mở rộng công khai các claim kỹ thuật cho đến khi có nguồn đã xác nhận.

| Priority | Product | URL | Current Content Quality | Specs Completeness | Meta Quality | Internal Links | Search Intent | Action |
|---|---|---|---|---|---|---|---|---|
| P0 | Cần cẩu chân nhện | https://phuduc.com/san-pham/can-cau-chan-nhen | THIN | PARTIAL (4 mức tải trọng variant) | NEEDS_REWRITE | WEAK: generic related-set | Commercial investigation | DATA_REQUIRED |
| P0 | Cẩu chân nhện chạy điện 1,5 tấn | https://phuduc.com/san-pham/cau-chan-nhen-chay-dien-1-5-tan | THIN | PARTIAL (1,5 tấn; note variant) | NEEDS_REWRITE | WEAK: generic related-set | Transactional exact-model | DATA_REQUIRED |
| P0 | Cẩu điện thủy lực 2 tấn | https://phuduc.com/san-pham/cau-dien-thuy-luc-2-tan | THIN | PARTIAL (2 tấn, 3 đốt, vươn 3 m, xoay 360°) | NEEDS_REWRITE | WEAK: generic related-set | Commercial investigation | DATA_REQUIRED |
| P0 | Cẩu thủy lực 500 kg | https://phuduc.com/san-pham/cau-thuy-luc-500-kg | THIN | PARTIAL (500 kg; note motor tời) | NEEDS_REWRITE | WEAK: generic related-set | Transactional exact-capacity | DATA_REQUIRED |
| P0 | Cẩu trục chạy điện có chân di chuyển | https://phuduc.com/san-pham/cau-truc-chay-dien-co-chan-di-chuyen | THIN | PARTIAL (2/3/5 tấn; 3 m × 3 m theo variant) | NEEDS_REWRITE | WEAK: generic related-set | Commercial comparison | DATA_REQUIRED |
| P1 | Cẩu trục cố định xoay 360 độ chạy điện | https://phuduc.com/san-pham/cau-truc-co-dinh-xoay-360-do-chay-dien | THIN | PARTIAL (1 và 2 tấn) | NEEDS_REWRITE | WEAK: generic related-set | Commercial exact-type | DATA_REQUIRED |
| P1 | Điều hòa 24V cho xe tải cắt nóc | https://phuduc.com/san-pham/dieu-hoa-24v-cho-xe-tai-cat-noc | THIN | PARTIAL (3 lựa chọn variant) | NEEDS_REWRITE | WEAK: generic related-set | Commercial comparison | DATA_REQUIRED |
| P1 | Máy bơm bê tông chạy dầu | https://phuduc.com/san-pham/may-bom-be-tong-chay-dau | THIN | PARTIAL (tên variant) | NEEDS_REWRITE | WEAK: generic related-set | Commercial investigation | DATA_REQUIRED |
| P1 | Máy cắt điều khiển từ xa | https://phuduc.com/san-pham/may-cat-dieu-khien-tu-xa | THIN | PARTIAL (tên variant; lead time hiển thị) | NEEDS_REWRITE | WEAK: generic related-set | Commercial exact-type | DATA_REQUIRED |
| P1 | Máy gắp hàng khí nén 100 kg | https://phuduc.com/san-pham/may-gap-hang-khi-nen-100-kg | THIN | PARTIAL (tải trọng 100 kg) | NEEDS_REWRITE | WEAK: generic related-set | Transactional exact-capacity | DATA_REQUIRED |
| P2 | Máy phát điện nạp cho xe tải chạy xăng 7L | https://phuduc.com/san-pham/may-phat-dien-nap-cho-xe-tai-chay-xang-7l | THIN; no gallery | PARTIAL (7 L và note variant) | NEEDS_REWRITE | WEAK: generic related-set | Transactional exact-use | DATA_REQUIRED |
| P1 | Máy phun vữa tường chạy điện | https://phuduc.com/san-pham/may-phun-vua-tuong-chay-dien | THIN | PARTIAL (tên variant) | NEEDS_REWRITE | WEAK: generic related-set | Commercial investigation | DATA_REQUIRED |
| P1 | Máy xúc lật 3 bánh gầu 0,2 m³ | https://phuduc.com/san-pham/may-xuc-lat-3-banh-gau-0-2-m3 | THIN | PARTIAL (gầu 0,2 m³) | NEEDS_REWRITE | WEAK: generic related-set | Transactional exact-capacity | DATA_REQUIRED |
| P0 | Máy xúc lật 4 bánh gầu 0,4 m³ | https://phuduc.com/san-pham/may-xuc-lat-4-banh-gau-0-4-m3 | THIN | PARTIAL (điện/diesel; gầu 0,4 m³) | NEEDS_REWRITE | WEAK: generic related-set | Commercial comparison | DATA_REQUIRED |
| P0 | Máy xúc lật gầu 0,7 m³ | https://phuduc.com/san-pham/may-xuc-lat-gau-0-7-m3 | THIN | PARTIAL (gầu 0,7 m³) | NEEDS_REWRITE | WEAK: generic related-set | Transactional exact-capacity | DATA_REQUIRED |
| P1 | Motor tời | https://phuduc.com/san-pham/motor-toi | THIN | PARTIAL (6.000/12.000 lbs) | NEEDS_REWRITE | WEAK: generic related-set | Commercial comparison | DATA_REQUIRED |
| P1 | Nhà container | https://phuduc.com/san-pham/nha-container | THIN | PARTIAL (20 ft/40 ft) | NEEDS_REWRITE | WEAK: generic related-set | Commercial comparison | DATA_REQUIRED |
| P1 | Xe chở hàng 3 bánh 1 tấn | https://phuduc.com/san-pham/xe-cho-hang-3-banh-1-tan | THIN | PARTIAL (điện/dầu; tải 1 tấn) | NEEDS_REWRITE | WEAK: generic related-set | Commercial comparison | DATA_REQUIRED |
| P1 | Xe chở hàng chạy điện | https://phuduc.com/san-pham/xe-cho-hang-chay-dien | THIN | PARTIAL (500/700 kg) | NEEDS_REWRITE | WEAK: generic related-set | Commercial comparison | DATA_REQUIRED |
| P0 | Xe nâng điện 0,8 tấn | https://phuduc.com/san-pham/xe-nang-dien-0-8-tan | THIN | PARTIAL (0,8 tấn) | NEEDS_REWRITE | WEAK: generic related-set | Transactional exact-capacity | DATA_REQUIRED |
| P0 | Xe nâng điện | https://phuduc.com/san-pham/xe-nang-dien | THIN | PARTIAL (1/1,5/2,5/3,5 tấn) | NEEDS_REWRITE | WEAK: generic related-set | Commercial category comparison | DATA_REQUIRED |
| P1 | Xe nâng dầu diesel 3 tấn | https://phuduc.com/san-pham/xe-nang-dau-diesel-3-tan | THIN | PARTIAL (3 tấn) | NEEDS_REWRITE | WEAK: generic related-set | Transactional exact-capacity | DATA_REQUIRED |
| P1 | Xe nâng điện 500 kg 48V | https://phuduc.com/san-pham/xe-nang-dien-500-kg-48v | THIN | PARTIAL (500 kg; 48V) | NEEDS_REWRITE | WEAK: generic related-set | Transactional exact-spec | DATA_REQUIRED |
| P1 | Xe nâng kết hợp chở hàng 1,5 tấn | https://phuduc.com/san-pham/xe-nang-ket-hop-cho-hang-1-5-tan | THIN | PARTIAL (1,5 tấn) | NEEDS_REWRITE | WEAK: generic related-set | Commercial exact-type | DATA_REQUIRED |
| P1 | Xe nâng pallet 2 tấn | https://phuduc.com/san-pham/xe-nang-pallet-2-tan | THIN | PARTIAL (2 tấn; lựa chọn có cân điện tử) | NEEDS_REWRITE | WEAK: generic related-set | Transactional exact-capacity | DATA_REQUIRED |
| P1 | Xe nâng tay | https://phuduc.com/san-pham/xe-nang-tay | THIN | PARTIAL (200/260 kg; chiều cao nâng theo variant) | NEEDS_REWRITE | WEAK: generic related-set | Commercial comparison | DATA_REQUIRED |
| P0 | Xe rùa bánh xích chở hàng điều khiển từ xa | https://phuduc.com/san-pham/xe-rua-banh-xich-cho-hang-dieu-khien-tu-xa | THIN | PARTIAL (điện; 500 kg/1/2 tấn) | NEEDS_REWRITE | WEAK: generic related-set | Commercial comparison | DATA_REQUIRED |
| P0 | Xe tải bánh xích chở hàng 1,8 tấn | https://phuduc.com/san-pham/xe-tai-banh-xich-cho-hang-1-8-tan | THIN | PARTIAL (diesel; 4 máy; 1,8 tấn) | NEEDS_REWRITE | WEAK: generic related-set | Transactional exact-capacity | DATA_REQUIRED |

## Dữ liệu cần bổ sung trước khi mở rộng copy

Mỗi sản phẩm cần một bộ fact sheet có nguồn (catalogue, manual, xác nhận nhà cung cấp hoặc thông số kỹ thuật đã duyệt) trước khi thêm thông tin kỹ thuật vào trang. Tối thiểu gồm:

1. Model/SKU, hãng hoặc xuất xứ nếu được phép công bố, tải trọng/dung tích áp dụng cho từng variant và điều kiện đo.
2. Kích thước, trọng lượng, công suất/nguồn điện/nhiên liệu, hành trình hoặc phạm vi làm việc **khi các chỉ số này có thật cho đúng variant**.
3. Phụ kiện đi kèm, điều kiện lắp đặt/vận hành, bảo hành, chứng từ và tồn kho/lead time được duyệt.
4. Hình ảnh từng variant có chú thích được kiểm duyệt; riêng máy phát điện nạp xe tải cần ít nhất ảnh sản phẩm chính trước khi xuất bản landing page mở rộng.

Không suy diễn thông số giữa các trang cùng nhóm. Ví dụ, không dùng chi tiết của cẩu điện thủy lực 2 tấn cho cẩu thủy lực 500 kg, hoặc của xe nâng điện tổng quát cho variant 500 kg 48V.
