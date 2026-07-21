# Homepage Migration Notes

## Strategy

Migration `2026_07_16_000000_standardize_storefront_content_schema`:

1. Thêm `users.is_admin`, `home_sections.type`, `home_sections.variant` và các index.
2. Đổi legacy key `benefits` thành `benefit_strip` khi key đích chưa tồn tại.
3. Thêm nullable `home_section_id`, foreign key và index; backfill theo `section_key`.
4. Copy `home.*` vào `hero`, `featured_products`, `energy_banner`, `latest_posts` chỉ khi có legacy settings.
5. Chỉ điền field/section còn trống; không ghi đè heading, config, CTA, order hoặc selection đã được Admin sửa.
6. Giữ `section_key` và các setting `home.*` trong PR này làm dữ liệu restore/compatibility. Chúng không còn được storefront đọc hoặc Settings UI chỉnh.

| Legacy setting | New section/path |
| --- | --- |
| `home.hero_title` | `hero.title` |
| `home.hero_subtitle` | `hero.subtitle` |
| `home.hero_image` | `hero.settings_json.image` |
| `home.hero_primary_*` | `hero.settings_json.primary_cta` |
| `home.hero_secondary_label` | `hero.settings_json.secondary_cta.label` |
| `home.featured_products_*` | `featured_products` title/config |
| `home.energy_*` | `energy_banner` heading/config/stats |
| `home.latest_posts_*` | `latest_posts` title/config |

## Rollback and production notes

Rollback drops the new FK/index/columns, restores `benefit_strip` to `benefits` when safe, and leaves legacy settings untouched. It was verified with `migrate:rollback --step=1` followed by `migrate` against the Docker MySQL database.

Deploy with a database backup and normal maintenance window. Do not run any UTF-8 data repair as part of this migration. `section_key` can be removed only in a later migration after all deployed code uses `home_section_id`.

`ProductionDefaultsSeeder` uses `firstOrCreate`; rerunning it does not overwrite Admin data. `DemoContentSeeder` is called only outside production.

## Admin bootstrap

The migration only adds `users.is_admin` with a `false` default and an index. It does not promote any account and does not contain a deployment-specific email, user ID, or username.

Production deployment must grant access explicitly to a confirmed existing account:

```bash
php artisan user:grant-admin admin@example.com --no-interaction
```

The command normalizes the lookup email, fails for an unknown account, never creates a user or changes a password, and is idempotent. Do not run `DatabaseSeeder` to grant production Admin access.

Production authorization preflight:

1. Back up the database.
2. Confirm the exact existing account email that must retain Admin access.
3. Run the migration.
4. Run `user:grant-admin` for the confirmed email.
5. Verify login and `/admin` access; verify a normal user still receives HTTP 403.
6. Complete deployment only after the authorization check passes.

## Migration immutability

The migration contains a frozen snapshot of the ten canonical section keys, types, default variants, and required fallback labels. It does not import `HomeSectionRegistry`, Eloquent models, application services, or frontend registry code. Future registry changes therefore cannot alter the result of this historical migration.

## Strict section item schema

Before submission, the Admin client retains only common technical item fields and fields declared by the section registry. The request rejects unknown, unsupported business, and unsupported metadata fields with HTTP 422. The controller independently persists only registry-whitelisted business and metadata fields.
