# Báo cáo BA — Hai thay đổi Media gần nhất

**Phạm vi:** Chỉ gồm Media Library và chuẩn hóa upload WebP.
**Code release cần deploy:** `e0cc85a2c280a22083cc98063dfcc2e92a7758a8`
**Production:** Chưa deploy, chưa chạm production DB hoặc dữ liệu sản phẩm.

## 1. Media Library dùng chung

- Có popup Media Library dùng chung cho sản phẩm, tin tức, cài đặt, trang chủ và trình soạn thảo.
- Hỗ trợ tìm kiếm, upload nhiều ảnh, chọn một/nhiều ảnh và phân loại bằng thư mục ảo.
- Trang Admin → Media hỗ trợ upload, tạo thư mục, lọc thư mục và chuyển Media giữa các thư mục.
- Sản phẩm hỗ trợ chọn album ảnh; biến thể vẫn liên kết ảnh qua `product_image_id`.
- Tin tức hỗ trợ ảnh đại diện và gallery có thứ tự.
- Media đang được sử dụng sẽ không thể xóa.

Migration cần chạy:

```text
2026_08_25_000001_create_media_folders_table
2026_08_25_000002_add_folder_id_to_media_libraries_table
2026_08_25_000003_create_post_media_table
```

## 2. Upload ảnh WebP

- JPEG/PNG được convert sang WebP trước khi lưu Media hoặc ảnh sản phẩm.
- File JPEG/PNG gốc không được lưu vào storage; chỉ có file WebP được tạo.
- `file_name`, `file_path`, `mime_type` đều phản ánh WebP, ví dụ `hero.webp` và `image/webp`.
- Nếu convert thất bại, file tạm được dọn và không tạo bản ghi dở dang.
- WebP giữ nguyên; GIF giữ nguyên để bảo toàn GIF động.
- Không có migration WebP và không tự động chuyển các Media JPEG/PNG cũ.

## Checklist deploy

1. Backup database production và `storage/app/public`.
2. Deploy code tại SHA `e0cc85a2c280a22083cc98063dfcc2e92a7758a8`.
3. Chạy `php artisan migrate --force` trước khi mở chức năng Media mới.
4. Build frontend/cache theo production runbook.
5. Smoke test: mở Media, tạo folder, upload PNG/JPEG, kiểm tra file `.webp`, thử gallery sản phẩm và gallery tin tức.

## Kiểm thử đã đạt

- Full PHP tests: `310 passed, 2 skipped`.
- MySQL upload regression: `PASS`.
- Frontend build, PHP syntax, audit PR3B và `git diff --check`: `PASS`.
