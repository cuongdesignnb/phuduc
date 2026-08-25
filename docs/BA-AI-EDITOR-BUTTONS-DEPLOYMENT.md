# BA deploy — nút AI trong editor sản phẩm và bài viết

## Phạm vi

Đã bổ sung thao tác AI trực tiếp trong trang quản trị:

- **Bài viết:** `Sinh bài viết + Meta` điền tiêu đề, tóm tắt, nội dung và các trường meta; `Sinh bài viết kèm ảnh + thumbnail` thực hiện thêm các bước trên, sinh ảnh, lưu WebP vào Media Library, gán ALT/caption, đưa ảnh đầu tiên làm thumbnail và cập nhật album bài viết.
- **Sản phẩm:** `Sinh mô tả + Meta` điền mô tả và meta SEO; `Sinh mô tả kèm ảnh` sinh thêm ảnh, lưu qua pipeline Media Library và gắn bản sao ảnh vào album sản phẩm.

## Backend và an toàn

- Dùng các endpoint AI hiện có: `admin.ai.content.generate` và `admin.ai.products.seo`.
- API key chỉ đọc phía server từ cấu hình AI đã mã hóa; không đưa key xuống trình duyệt.
- Nội dung/ảnh vẫn đi qua sanitizer và pipeline WebP + ALT/caption hiện có.
- Không thay đổi schema, giá, SKU, biến thể hay dữ liệu sản phẩm hiện hữu trong phần bổ sung này.

## Triển khai

1. Deploy release chứa commit `9b00639` và các migration AI đã có trong release trước.
2. Chạy `php artisan migrate --force` nếu môi trường chưa chạy các migration AI.
3. Build frontend (`npm run build`) và reload PHP app/queue worker.
4. Kiểm tra cấu hình provider/key tại **Quản trị → AI nội dung & SEO**. Nếu dùng lịch sinh bài, giữ queue worker và scheduler hoạt động.

## Kết quả kiểm thử

```text
PHP_SYNTAX=PASS
git diff --check=PASS
FULL_TESTS=PASS (320 passed, 2 skipped, 2643 assertions)
MYSQL_REGRESSION=PASS (5 tests)
FRONTEND_BUILD=PASS
AUDITS=PASS (storefront-theme, pr2c-commerce, pr3a-admin, pr3b-admin-content, pr3c-admin-operations)
BROWSER_SMOKE=PASS
PRODUCTION_TOUCHED=NO
PRODUCTION_DATABASE_TOUCHED=NO
```

Rollback chỉ cần quay về release trước ở tầng ứng dụng; không xóa media, sản phẩm hoặc dữ liệu AI đã sinh.
