# Phú Đức — Release 5 sản phẩm SEO lên production

## Phạm vi release

- Migration thêm `product_images.alt_text`.
- Seeder `Database\\Seeders\\PhuDucSeoProductSeeder` cho toàn bộ 28 trang sản phẩm và 48 biến thể trong manifest: meta title, meta description, mô tả, thông số đã xác nhận từ listing, liên kết nội bộ và alt text riêng.
- Seeder cập nhật theo slug của catalogue hiện hữu. Không xóa sản phẩm, biến thể hoặc ảnh gallery đang có; gallery production được giữ nguyên và mỗi ảnh nhận alt text theo đúng trang sản phẩm.

## Điều kiện trước khi chạy

1. Backup database production và toàn bộ `storage/app/public`.
2. Bản release phải bao gồm `docs/phuduc-product-normalization.json`; đây là manifest 28 sản phẩm/48 biến thể được seeder đọc làm nguồn dữ liệu.
3. Xác nhận `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` là domain chính thức và `storage/app/public` có quyền ghi.
4. Không dùng `migrate:fresh`, `db:wipe`, `migrate:reset` hoặc `DatabaseSeeder` cho thao tác này.

## Lệnh chạy trên release server

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build

php artisan migrate --force
php artisan storage:link
php artisan db:seed --class='Database\Seeders\PhuDucSeoProductSeeder' --force
php artisan down --render="errors::503" --retry=60
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

Lệnh seeder có thể chạy lại an toàn: không tạo thêm sản phẩm, biến thể, ảnh gallery hoặc bản ghi Media trùng lặp. Những gallery đã có trên production được giữ nguyên.

Khi chạy trên database local trống gallery, seeder chỉ dùng `https://phuduc.com` làm nguồn ảnh công khai của chính Phú Đức. Trên production có gallery hiện hữu, bước này không tải lại, thay thế hay xóa ảnh.

## Smoke test sau release

Kiểm tra HTTP 200 cho `/san-pham` và tối thiểu các URL đại diện:

- `/san-pham/cau-dien-thuy-luc-2-tan`
- `/san-pham/can-cau-chan-nhen`
- `/san-pham/cau-truc-chay-dien-co-chan-di-chuyen`
- `/san-pham/may-xuc-lat-4-banh-gau-0-4-m3`
- `/san-pham/xe-nang-dien`

Trên mỗi trang, xác nhận gallery hiện có vẫn hiển thị, ảnh có alt text riêng, meta title/meta description xuất hiện trong HTML server và mục “Thiết bị liên quan” hiển thị các liên kết nội bộ.
