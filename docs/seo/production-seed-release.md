# Phú Đức — Release 5 sản phẩm SEO lên production

## Phạm vi release

- Migration thêm `product_images.alt_text`.
- Seeder `Database\\Seeders\\PhuDucSeoProductSeeder` cho năm trang sản phẩm, 15 ảnh WebP, meta title, meta description, thông số kỹ thuật, liên kết nội bộ và alt text riêng.
- Seeder chỉ cập nhật năm SKU `PD-CRANE-HYD-1000`, `PD-SPIDER-5T`, `PD-GANTRY-MOBILE`, `PD-LOADER-904`, `PD-FORKLIFT-ELECTRIC-4W`. Không xóa hoặc ghi vào sản phẩm khác.

## Điều kiện trước khi chạy

1. Backup database production và toàn bộ `storage/app/public`.
2. Bản release phải bao gồm `docs/seo/alibaba/search-images/`; đây là ảnh nguồn WebP để seeder chép vào Media Library và gallery sản phẩm.
3. Xác nhận `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` là domain chính thức và `storage/app/public` có quyền ghi.
4. Không dùng `migrate:fresh`, `db:wipe`, `migrate:reset` hoặc `DatabaseSeeder` cho thao tác này.

## Lệnh chạy trên release server

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build

php artisan down --render="errors::503" --retry=60
php artisan migrate --force
php artisan storage:link
php artisan db:seed --class='Database\Seeders\PhuDucSeoProductSeeder' --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

Lệnh seeder có thể chạy lại an toàn: không tạo thêm sản phẩm, ảnh gallery hoặc bản ghi Media trùng lặp cho năm SKU trên.

## Smoke test sau release

Kiểm tra HTTP 200 cho `/san-pham` và năm URL:

- `/san-pham/cau-dien-thuy-luc-1-tan-xoay-360-do`
- `/san-pham/can-cau-chan-nhen-banh-xich-5-tan`
- `/san-pham/cau-truc-chay-dien-co-chan-di-chuyen`
- `/san-pham/may-xuc-lat-mini-banh-lop-diesel-904`
- `/san-pham/xe-nang-dien-doi-trong-4-banh`

Trên mỗi trang, xác nhận gallery có ba ảnh WebP, ảnh có alt text riêng, meta title/meta description xuất hiện trong HTML server và mục “Thiết bị liên quan” hiển thị các liên kết nội bộ.
