# Báo cáo phương án triển khai PhuDuc dành cho BA

**Ngày báo cáo:** 25/08/2026
**Nguồn rà soát:** `codex://threads/019f895f-6f84-7a71-82e0-2917612a2807`
**Phạm vi:** Quy trình đã được thực hiện trong thread, hai thay đổi Media mới nhất và mức độ sẵn sàng cho triển khai production.

## 1. Kết luận điều hành

Thread được rà soát **chưa thực hiện deploy production**. Quy trình đã có là:

1. Làm việc trên repository GitHub `cuongdesignnb/phuduc`.
2. Chạy ứng dụng và QA trong Docker local.
3. Kiểm thử, build frontend, browser QA và Lighthouse.
4. Commit và push lên branch tính năng.
5. Mở Draft Pull Request trên GitHub.
6. Khi được duyệt, squash merge vào `main` và kiểm tra SHA/tree parity.

Không có bằng chứng về việc đã triển khai lên VPS, cPanel, Vercel, cloud hosting, server riêng hoặc production domain.

## 2. Báo cáo công việc đã thực hiện

### 2.1. Nhập và chuẩn hóa dữ liệu sản phẩm

- Đã nhập **28 sản phẩm** từ thư mục dữ liệu sản phẩm vào website.
- Đã duy trì **48 biến thể** cùng tên, giá, tồn kho, ghi chú và trạng thái theo dữ liệu nguồn.
- Đã nhập **380 ảnh sản phẩm** vào Media Library.
- Ảnh được chuyển sang WebP, lưu theo thư mục slug sản phẩm và có alt text SEO.
- Đã chuẩn hóa slug, tên sản phẩm và dữ liệu giá trong manifest: [phuduc-product-normalization.json](phuduc-product-normalization.json).
- Đã giữ dữ liệu ảnh 360 riêng với gallery ảnh thường.
- Đã tạo tài liệu/mã import để có thể chạy lại theo folder sản phẩm mà không dùng thao tác xóa database.

### 2.2. Biến thể và gallery sản phẩm

- Đã bổ sung bảng dữ liệu `product_variants` và quan hệ với sản phẩm.
- Đã bổ sung liên kết ảnh đại diện cho biến thể qua `product_image_id`.
- Đã gán ảnh đại diện tự động cho 47/48 biến thể có gallery; 1 biến thể không có ảnh nguồn nên để trống.
- Trang quản trị có trường **Ảnh đại diện biến thể** để BA/Content chỉnh lại liên kết khi ảnh nguồn không có metadata biến thể.
- Gallery chi tiết sản phẩm đã đổi từ lưới nhiều hàng sang slider thumbnail ngang phía dưới ảnh chính.
- Khi chọn biến thể, ảnh chính tự chuyển tới ảnh đại diện của biến thể.

### 2.3. Giao diện, font và nội dung

- Đã sửa lỗi font tiếng Việt; storefront dùng font có fallback an toàn, không còn hiện ký tự/font sai.
- Đã chuẩn hóa các section trang chủ, menu và luồng hiển thị nội dung theo cấu trúc dữ liệu hiện tại.
- Đã rà lại trang chi tiết sản phẩm, gallery, giá biến thể và trạng thái hiển thị.

### 2.4. SEO và kiểm soát lập chỉ mục

- Đã bổ sung checkbox tại **Admin → Cài đặt → SEO**: **Chặn công cụ tìm kiếm lập chỉ mục**.
- Khi bật, website phát `noindex, nofollow` qua meta robots và `X-Robots-Tag`.
- Khi tắt, website trở về `index, follow`.
- Mặc định hiện tại là **tắt**, để không vô tình chặn website khi mở production.

### 2.5. Migration, build và kiểm tra thực tế

- Đã chạy các migration bổ sung cho biến thể, ảnh đại diện biến thể và setting SEO.
- Build frontend trong container đạt: **Vite 910 modules**.
- Browser QA trang sản phẩm đạt: HTTP 200, 14 thumbnail nằm cùng một hàng ngang, không có request lỗi.
- Đã kiểm tra chọn biến thể thứ hai làm ảnh chính thay đổi đúng.
- Đã kiểm tra bật/tắt noindex thực tế trên `/san-pham`: header và meta thay đổi đúng.
- `php -l` và `git diff --check` đạt trong các phạm vi đã chạm.

### 2.6. Dữ liệu và môi trường đã sử dụng

- Tất cả thao tác hiện tại thực hiện trên môi trường local `D:\phuduc` và Docker local.
- Database local được giữ nguyên; không dùng production database.
- Không gửi dữ liệu khách hàng, credential production hoặc dữ liệu thật lên dịch vụ QA.
- Chưa thực hiện backup production, migration production hoặc deploy production.

### 2.7. Phần mới 1 — Media Library dùng chung kiểu WordPress

Đã merge tại PR #12, `main` sau merge là `994dacc2ed5b480a818e119d03dc187f251e302f`.

- Đã thay picker Media cũ bằng popup dùng chung cho sản phẩm, bài viết, cài đặt, nội dung trang chủ và trình soạn thảo.
- Popup hỗ trợ tìm kiếm, lọc ảnh, upload nhiều tệp, chọn một/nhiều ảnh và giữ lựa chọn khi chuyển trang.
- Trang **Admin → Media** có upload trực tiếp, tạo thư mục ảo, lọc thư mục và chuyển nhiều Media giữa các thư mục.
- Thư mục chỉ là phân loại trong database; hệ thống không tự di chuyển vật lý file đang có trong storage.
- Sản phẩm có thể chọn nhiều ảnh từ Media để tạo bản sao sở hữu riêng trong gallery sản phẩm; liên kết ảnh biến thể vẫn dùng `product_image_id` hiện hữu.
- Bài viết có ảnh đại diện và gallery có thứ tự; gallery được hiển thị ở trang chi tiết tin tức.
- Media đang được bài viết, sản phẩm, setting, homepage hoặc rich content tham chiếu vẫn được bảo vệ khỏi xóa.

Migration cần chạy khi release:

```text
2026_08_25_000001_create_media_folders_table
2026_08_25_000002_add_folder_id_to_media_libraries_table
2026_08_25_000003_create_post_media_table
```

Các migration này là additive: tạo bảng mới, thêm khóa ngoại nullable và không xóa dữ liệu sản phẩm/media hiện hữu.

### 2.8. Phần mới 2 — Chuẩn hóa upload ảnh WebP

Đã merge tại PR #13, `main` hiện tại là:

```text
e0cc85a2c280a22083cc98063dfcc2e92a7758a8
```

- Upload JPEG/PNG qua Media Library hoặc upload ảnh trực tiếp ở sản phẩm đều đi qua `AdminImageStorageService`.
- Ảnh được convert sang WebP trước khi tạo bản ghi `media_libraries` hoặc `product_images`.
- File gốc JPEG/PNG không được lưu vào `storage`; storage chỉ nhận file WebP. Vì vậy không cần job xóa file gốc riêng.
- Nếu convert/ghi WebP thất bại, file WebP tạm được dọn và transaction không tạo bản ghi Media dở dang.
- `file_name`, `file_path` và `mime_type` của ảnh được convert đều phản ánh WebP, ví dụ `hero.webp` / `image/webp`.
- WebP upload sẵn được giữ nguyên. GIF hiện giữ nguyên định dạng để bảo toàn GIF động; không nằm trong phạm vi convert sang WebP.
- Thay đổi PR #13 không có migration và không chạy backfill tự động cho các Media JPEG/PNG cũ đã tồn tại trước release.

Nếu BA yêu cầu chuyển toàn bộ Media cũ sang WebP, cần tạo một maintenance command riêng có manifest, backup storage và kiểm kê tham chiếu; không nên thực hiện trong release này.

## 3. Quy trình Docker local đã được xác nhận

### Cấu hình

- Compose services: `app`, `db`.
- App local: `http://localhost:8741`.
- MySQL local container: `phuduc-db`.
- Database local mặc định: `phuduc`.
- Port MySQL host: `3641`.
- Các credential `phuduc/secret` chỉ là credential development trong Compose, không được dùng cho production.

### Chuỗi thao tác QA

```powershell
docker compose config
docker compose config --services
docker compose up -d --build
docker compose ps

docker compose exec -T app php artisan migrate --force
docker compose exec -T app npm run build

# Chạy test/audit theo phạm vi cần xác nhận
docker compose exec -T app php artisan test
docker compose exec -T app npm run audit:storefront-theme

# Sau QA, dừng stack nhưng giữ volume dữ liệu khi cần
docker compose down
docker compose ps
```

Trong thread, database QA và fixture tạm được tạo riêng, kiểm tra xong thì drop; Docker được tắt và không còn container dự án chạy.

## 4. Cách đưa code lên GitHub đã dùng

Quy trình code delivery trong thread:

```powershell
git status --short
git diff --check
git add <files>
git commit -m "<message>"
git push origin <feature-branch>
```

Sau đó mở Draft PR, cập nhật evidence/validation, review, chuyển Ready khi được ủy quyền và squash merge vào `main`. Sau merge, thread kiểm tra:

- SHA local và remote.
- Tree parity.
- Worktree sạch.
- Post-merge test/build/smoke.
- Xóa branch làm việc.

Đây là quy trình **source control/review**, không phải quy trình deploy ứng dụng lên production server.

## 5. Những gì đã được xác nhận trong thread

- Không dùng production database.
- Không mutate production data.
- Không dùng production credentials.
- Không triển khai production.
- Docker QA dùng MySQL/SQLite cô lập.
- Build Vite chạy thành công trong container.
- Các đợt QA có test, browser QA, HTTP smoke, migration rollback/re-run và audit theo phạm vi.
- GitHub Actions được ghi nhận là chưa cấu hình hoặc không có checks được báo cáo.

## 6. Rủi ro nếu dùng Compose hiện tại làm production

Compose/Dockerfile hiện tại đang phục vụ development/QA, chưa phải cấu hình production hoàn chỉnh:

- `APP_ENV=local` và `APP_DEBUG=true`.
- `APP_URL=http://localhost:8741`.
- Entrypoint chạy `php artisan serve`, `queue:listen` và `npm run dev`.
- Source được bind-mount trực tiếp vào container.
- Không có reverse proxy HTTPS như Nginx/Traefik được mô tả.
- Không có cấu hình domain, TLS/SSL, backup tự động, monitoring, log rotation hoặc rollback production.
- Không có CI/CD workflow đã được xác nhận.

Vì vậy không nên chỉ đổi port hoặc chạy nguyên file Compose hiện tại trên Internet để coi là production deployment.

## 7. Các quyết định BA cần xác nhận trước khi deploy thật

| Hạng mục | Cần xác nhận |
|---|---|
| Hạ tầng | VPS, shared hosting/cPanel, Vercel hay nền tảng khác |
| Domain | Domain production, DNS và HTTPS/SSL |
| Runtime | PHP version, Node/build strategy, process manager và web server |
| Database | MySQL production, host/port, backup và quyền truy cập |
| Media | Nơi lưu `storage/app/public`, dung lượng, backup và CDN nếu có |
| Secrets | `APP_KEY`, DB password, mail, queue/cache và các secret production |
| Migration | Cửa sổ chạy `php artisan migrate --force`, người phê duyệt và kế hoạch rollback |
| Admin | Tài khoản admin production được cấp quyền riêng, không seed mật khẩu mẫu |
| Release | Chiến lược zero/minimal downtime và phiên bản cần release |
| Rollback | Người chịu trách nhiệm, bản backup trước release và cách quay lại image/commit trước |
| SEO | Trạng thái checkbox noindex trước khi mở site công khai |

## 7.1. Phương án deploy cho hai phần Media mới

### Trước deploy

1. BA/DevOps xác nhận release SHA `e0cc85a2c280a22083cc98063dfcc2e92a7758a8`.
2. Backup database production và toàn bộ `storage/app/public`.
3. Kiểm tra dung lượng storage vì upload ảnh mới sẽ tạo file WebP và không giữ bản JPEG/PNG upload tạm.
4. Xác nhận quyền ghi của process ứng dụng vào `storage/app/public` và quyền chạy migration.
5. Xác nhận checkbox **Chặn công cụ tìm kiếm lập chỉ mục** đang ở trạng thái mong muốn trước khi mở site.

### Trong deploy

```powershell
# Chạy trong release container/server, sau khi code đã ở SHA mới
php artisan down --render="errors::503" --retry=60
php artisan migrate --force
php artisan storage:link
npm run build                 # hoặc dùng frontend build artifact đã được CI tạo
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

Thứ tự quan trọng là chạy migration trước khi mở các màn hình Media mới. Không chạy `migrate:fresh`, không xóa bảng cũ và không chạy lại lệnh import 28 sản phẩm trong release này.

### Sau deploy — smoke test bắt buộc

| Luồng | Kết quả cần xác nhận |
|---|---|
| Admin → Media | Mở trang không 500; danh sách Media cũ vẫn hiển thị |
| Upload PNG/JPEG | Bản ghi có `mime_type=image/webp`, `file_name` kết thúc `.webp`, storage không có file nguồn tương ứng |
| Folder | Tạo folder, upload vào folder, lọc folder và chuyển Media hoạt động |
| Sản phẩm | Popup chọn nhiều ảnh, gallery hiển thị; biến thể vẫn đổi đúng ảnh đại diện |
| Tin tức | Chọn ảnh đại diện và gallery, lưu rồi mở detail public |
| Xóa Media | Media đang được tham chiếu bị chặn xóa; Media không tham chiếu xóa được |
| Public site | `/`, `/san-pham`, `/tin-tuc` HTTP 200; kiểm tra robots theo setting |

### Rollback

- Nếu lỗi code nhưng database ổn định: rollback application image/code về release trước, giữ nguyên ba migration mới vì schema additive và code cũ không bắt buộc đọc chúng.
- Không chạy migration `down` trong rollback thông thường; thao tác đó có thể xóa folder/gallery mới tạo sau release.
- Nếu lỗi mất file hoặc upload: phục hồi storage từ backup và kiểm tra lại `media_libraries`, `product_images`, `post_media` trước khi mở site.
- Mọi rollback database phải có phê duyệt riêng của BA/DevOps và backup đối chiếu.

## 8. Trạng thái bàn giao cho BA

```text
LOCAL_DOCKER_QA=AVAILABLE
GITHUB_BRANCH_PR_WORKFLOW=AVAILABLE
CURRENT_MAIN_SHA=e0cc85a2c280a22083cc98063dfcc2e92a7758a8
MEDIA_LIBRARY_PR=12_MERGED
WEBP_UPLOAD_PR=13_MERGED
PRODUCTION_DEPLOYMENT_DOCUMENTED_IN_THREAD=NO
PRODUCTION_DEPLOYMENT_EXECUTED=NO
PRODUCTION_DATABASE_USED=NO
CI_CD_CONFIRMED=NO
PRODUCTION_HOST_CONFIRMED=NO
READY_FOR_PRODUCTION_DEPLOYMENT=NO
BLOCKER=Chưa có lựa chọn hạ tầng và runbook production được BA phê duyệt
```

## 9. Tài liệu kỹ thuật liên quan

- [docker-compose.yml](../docker-compose.yml)
- [Dockerfile](../Dockerfile)
- [docker-entrypoint.sh](../docker-entrypoint.sh)
- [PR2B pull-request notes](refactor/pr2b/PR2B-PULL-REQUEST.md)
- [PR2C test results](refactor/evidence/pr2c/test-results.md)
- [PR #12 — Media Library](https://github.com/cuongdesignnb/phuduc/pull/12)
- [PR #13 — WebP upload contract](https://github.com/cuongdesignnb/phuduc/pull/13)
