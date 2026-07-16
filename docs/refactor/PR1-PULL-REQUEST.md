## Summary

Chuẩn hóa data contract storefront và hợp nhất quản trị homepage thành một nguồn dữ liệu canonical.

## Major changes

- Canonical `site / navigation / page.sections` contract.
- Ordered homepage section rendering through a 10-section registry.
- Schema-driven Admin homepage editor with toggle, variant, items, and reorder controls.
- Manual/latest featured product and post sources.
- Product specification normalization and real review aggregates.
- Database-backed branding and favicon binding.
- Production-safe, idempotent seeders.
- Explicit Admin authorization.
- MySQL-compatible migration/backfill with legacy `home.*` preservation.

## Data behavior

### Manual products

- Preserve configured `product_ids` order.
- Skip inactive or deleted products.
- Do not auto-fill, substitute, or switch to latest.

### Latest products

- Active products only.
- Newest first.
- Respect configured limit.

### Empty state

- No mock, sample, fallback, or auto-filled content.
- Hide unusable empty list sections.
- Hero may render with a useful title and no image; energy banner may render useful text with no decorative image.

## Database

- Migration: `2026_07_16_000000_standardize_storefront_content_schema.php`.
- Backfills canonical sections from legacy data without deleting legacy settings.
- Adds safe `is_admin` default, homepage indexes, native JSON columns, and item foreign key.
- Isolated MySQL 8 verification: fresh migrate PASS, rollback PASS, remigrate PASS, integrity PASS.
- Existing Admin content and access are preserved; normal users are not promoted; production seed reruns create no users.

## Testing

- PHP: 40 tests, 179 assertions - PASS.
- Frontend build: 863 modules - PASS.
- Pint changed-file gate: 32 files - PASS.
- MySQL relevant suite after rollback/remigrate: 14 tests, 114 assertions - PASS.
- Manual QA: storefront desktop/mobile, Admin sections/editor/manual picker/reorder, logo, favicon, restart persistence - PASS.
- npm lint: not configured.

## Known limitations

- Node 20.15.1 is below Vite 7's declared engine range; build passes.
- Plain `npm install` has a pre-existing Vite 7 / plugin Vue 5 peer mismatch; the committed lock installs using `npm ci --legacy-peer-deps`.
- Existing dependency vulnerabilities are deferred; no dependency file is changed in this PR.
- Full-repository Pint has 40 pre-existing unrelated style findings; changed PHP files pass.
- UTF-8 debt outside homepage remains deferred.
- Design-system/design-token refactor is deferred to PR 2.
- Product `is_featured` is not introduced in this closure.
- GitHub Actions is not configured in this repository.

## Screenshots

- `docs/refactor/evidence/pr1-closure/screenshots/homepage-desktop.png`
- `docs/refactor/evidence/pr1-closure/screenshots/homepage-mobile.png`
- `docs/refactor/evidence/pr1-closure/screenshots/admin-section-tabs.png`
- `docs/refactor/evidence/pr1-closure/screenshots/hero-editor.png`
- `docs/refactor/evidence/pr1-closure/screenshots/featured-products-manual-picker.png`
- `docs/refactor/evidence/pr1-closure/screenshots/section-reorder.png`
- `docs/refactor/evidence/pr1-closure/screenshots/branding-logo.png`
- `docs/refactor/evidence/pr1-closure/screenshots/favicon-setting.png`
- `docs/refactor/evidence/pr1-closure/screenshots/favicon-http-asset.png`

## Rollback plan

1. Revert the PR commit to restore the previous storefront/Admin code.
2. Run `php artisan migrate:rollback --step=1` for the PR migration if database rollback is required.
3. Legacy `home.*` settings are intentionally preserved, so the prior storefront source remains available after rollback.
4. Take the normal environment snapshot before any production rollback; do not run the verification-database commands against production.
