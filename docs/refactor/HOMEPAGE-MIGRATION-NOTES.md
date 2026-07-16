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
