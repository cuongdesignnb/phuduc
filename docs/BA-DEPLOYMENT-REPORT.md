# Báo cáo phương án triển khai PhuDuc dành cho BA

**Ngày báo cáo:** 22/08/2026
**Nguồn rà soát:** `codex://threads/019f895f-6f84-7a71-82e0-2917612a2807`
**Phạm vi:** Quy trình đã được thực hiện trong thread và mức độ sẵn sàng cho triển khai production.

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

## 8. Trạng thái bàn giao cho BA

```text
LOCAL_DOCKER_QA=AVAILABLE
GITHUB_BRANCH_PR_WORKFLOW=AVAILABLE
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
