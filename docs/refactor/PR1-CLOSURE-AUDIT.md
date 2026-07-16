# PR 1 Closure Audit

## Repository

```text
BASE_SHA: 2a30a1ba60f64d59c5ee98eeadfe14098a378375
BRANCH: refactor/storefront-data-contract
FILES_CHANGED: 79
MIGRATIONS_ADDED: 1
MODELS_CHANGED: HomeSection, HomeSectionItem, Product, User
SERVICES_ADDED: HomePageDataService, MediaUrlService, NavigationService, ProductPresentationService, SiteConfigurationService, StorefrontPageService
CONTROLLERS_CHANGED: Admin/HomeContentController, Admin/SettingController, Guest/PageController
ROUTES_CHANGED: routes/web.php (admin middleware enforcement and homepage admin routes)
ADMIN_COMPONENTS_CHANGED: HomeContent/Index.vue, Setting/Index.vue, SaveHomeContentRequest, EnsureUserIsAdmin
STOREFRONT_COMPONENTS_CHANGED: GuestPageLayout.vue, Guest/Home.vue, HomeSectionRenderer.vue, 10 registry section components
TESTS_ADDED: 5 files; final suite 40 tests / 179 assertions
```

## Scope audit

The original implementation set contained 54 application, migration, component, test, and refactor-document files. Closure adds only the required audit/evidence files and screenshots. The accidental `package.json` / `package-lock.json` dependency upgrade was reverted to `origin/main`. No checkout behavior, unrelated blog behavior, Docker configuration, broad UI redesign, design-token refactor, global UTF-8 cleanup, or `is_featured` field is included.

The complete final `git diff --name-status origin/main` output is included below and mirrored in `evidence/pr1-closure/changed-files.txt`.

```text
M	app/Http/Controllers/Admin/HomeContentController.php
M	app/Http/Controllers/Admin/SettingController.php
M	app/Http/Controllers/Guest/PageController.php
A	app/Http/Middleware/EnsureUserIsAdmin.php
M	app/Http/Middleware/HandleInertiaRequests.php
A	app/Http/Requests/Admin/SaveHomeContentRequest.php
M	app/Models/HomeSection.php
M	app/Models/HomeSectionItem.php
M	app/Models/Product.php
M	app/Models/User.php
M	app/Providers/AppServiceProvider.php
A	app/Services/Storefront/HomePageDataService.php
A	app/Services/Storefront/MediaUrlService.php
A	app/Services/Storefront/NavigationService.php
A	app/Services/Storefront/ProductPresentationService.php
A	app/Services/Storefront/SiteConfigurationService.php
A	app/Services/Storefront/StorefrontPageService.php
A	app/Support/Homepage/HomeSectionRegistry.php
M	bootstrap/app.php
M	database/factories/UserFactory.php
A	database/migrations/2026_07_16_000000_standardize_storefront_content_schema.php
M	database/seeders/DatabaseSeeder.php
A	database/seeders/DemoContentSeeder.php
M	database/seeders/HomeContentSeeder.php
A	database/seeders/ProductionDefaultsSeeder.php
M	database/seeders/SettingSeeder.php
A	docs/refactor/HOMEPAGE-ADMIN-MAPPING.md
A	docs/refactor/HOMEPAGE-CONTENT-CONTRACT.md
A	docs/refactor/HOMEPAGE-MIGRATION-NOTES.md
A	docs/refactor/PR1-CLOSURE-AUDIT.md
A	docs/refactor/PR1-PULL-REQUEST.md
A	docs/refactor/UTF8-DATA-DEBT.md
A	docs/refactor/evidence/pr1-closure/changed-files.txt
A	docs/refactor/evidence/pr1-closure/git-diff-stat.txt
A	docs/refactor/evidence/pr1-closure/git-status-before.txt
A	docs/refactor/evidence/pr1-closure/hardcode-search.txt
A	docs/refactor/evidence/pr1-closure/legacy-settings-search.txt
A	docs/refactor/evidence/pr1-closure/manual-qa.md
A	docs/refactor/evidence/pr1-closure/mysql-integrity-check.txt
A	docs/refactor/evidence/pr1-closure/mysql-migrate-status-before.txt
A	docs/refactor/evidence/pr1-closure/mysql-migrate.txt
A	docs/refactor/evidence/pr1-closure/mysql-relevant-tests.txt
A	docs/refactor/evidence/pr1-closure/mysql-remigrate.txt
A	docs/refactor/evidence/pr1-closure/mysql-rollback.txt
A	docs/refactor/evidence/pr1-closure/npm-build.txt
A	docs/refactor/evidence/pr1-closure/php-tests.txt
A	docs/refactor/evidence/pr1-closure/pint.txt
A	docs/refactor/evidence/pr1-closure/route-list-admin.txt
A	docs/refactor/evidence/pr1-closure/screenshots/admin-section-tabs.png
A	docs/refactor/evidence/pr1-closure/screenshots/branding-logo.png
A	docs/refactor/evidence/pr1-closure/screenshots/favicon-http-asset.png
A	docs/refactor/evidence/pr1-closure/screenshots/favicon-setting.png
A	docs/refactor/evidence/pr1-closure/screenshots/featured-products-manual-picker.png
A	docs/refactor/evidence/pr1-closure/screenshots/hero-editor.png
A	docs/refactor/evidence/pr1-closure/screenshots/homepage-desktop.png
A	docs/refactor/evidence/pr1-closure/screenshots/homepage-mobile.png
A	docs/refactor/evidence/pr1-closure/screenshots/section-reorder.png
A	resources/js/Components/Home/HomeSectionRenderer.vue
A	resources/js/Components/Home/Sections/BenefitStripSection.vue
A	resources/js/Components/Home/Sections/CategoryCardsSection.vue
A	resources/js/Components/Home/Sections/ConsultationStepsSection.vue
A	resources/js/Components/Home/Sections/EnergyBannerSection.vue
A	resources/js/Components/Home/Sections/HeroSection.vue
A	resources/js/Components/Home/Sections/IndustrySolutionsSection.vue
A	resources/js/Components/Home/Sections/PartnersSection.vue
A	resources/js/Components/Home/Sections/PostCollectionSection.vue
A	resources/js/Components/Home/Sections/ProductCollectionSection.vue
A	resources/js/Components/Home/Sections/TestimonialsSection.vue
M	resources/js/Layouts/GuestPageLayout.vue
M	resources/js/Pages/Admin/HomeContent/Index.vue
M	resources/js/Pages/Admin/Setting/Index.vue
M	resources/js/Pages/Guest/Home.vue
M	resources/views/app.blade.php
M	routes/web.php
A	tests/Feature/Admin/AdminAuthorizationTest.php
A	tests/Feature/Admin/HomeContentSaveTest.php
A	tests/Feature/Seeders/ProductionDefaultsSeederTest.php
A	tests/Feature/Storefront/HomepageContractTest.php
A	tests/Unit/Storefront/MediaUrlServiceTest.php
```

## Database and migration

- Added `2026_07_16_000000_standardize_storefront_content_schema.php`.
- MySQL 8 verification used isolated database `phuduc_pr1_verification`; the primary local database was never rolled back.
- Fresh migrate, production-safe seed, rollback latest, remigrate, and relevant MySQL tests all pass.
- Native JSON columns, foreign key, unique/index constraints, legacy item backfill, zero orphans, zero duplicate section keys, and legacy settings preservation are verified.
- `is_admin` defaults to `0`; the seeded existing admin remained admin, the normal user remained non-admin, and production seed reruns created no account.

## Legacy `home.*` classification

```text
MIGRATION_COMPATIBILITY: PRESENT (read only for one-time backfill/rollback compatibility)
DOCUMENTATION: PRESENT
LEGACY_READ: migration compatibility only
LEGACY_WRITE: migration compatibility only; Settings Admin blocks all home.* writes
ACTIVE_STOREFRONT_USAGE: 0
LEGACY_WRITE_FROM_SETTINGS: 0
```

No section has two active data sources. Legacy values remain stored for rollback but the storefront reads the canonical homepage tables only.

## Section registry review

| Section | Toggle | Sort | Variant | Section validation | Empty state |
| --- | ---: | ---: | ---: | ---: | ---: |
| `hero` | Yes | Yes | Yes | Yes - heading/image/CTA schema | Yes - title may render without image |
| `category_cards` | Yes | Yes | Yes | Yes - registry item fields | Yes - hide when items empty |
| `benefit_strip` | Yes | Yes | Yes | Yes - registry item fields | Yes - hide when items empty |
| `featured_products` | Yes | Yes | Yes | Yes - source/limit/product IDs | Yes - hide when no valid products |
| `energy_banner` | Yes | Yes | Yes | Yes - heading/stats/image schema | Yes - text may render without decorative image |
| `industry_solutions` | Yes | Yes | Yes | Yes - registry item fields | Yes - hide when items empty |
| `testimonials` | Yes | Yes | Yes | Yes - registry item fields | Yes - hide when items empty |
| `partners` | Yes | Yes | Yes | Yes - registry item fields | Yes - hide when items empty |
| `latest_posts` | Yes | Yes | Yes | Yes - source/limit/post IDs | Yes - hide when no valid posts |
| `consultation_steps` | Yes | Yes | Yes | Yes - registry item fields | Yes - hide when items empty |

Empty collections never render mock cards, sample content, or auto-filled items. The rule is documented in `HOMEPAGE-CONTENT-CONTRACT.md` and covered by the manual-product empty-state test.

## Featured Products behavior

- Manual: preserves `product_ids` order; drops inactive/deleted products; does not fill, replace, or switch source.
- Latest: active products only; newest first; honors limit; inactive products never fill the result.
- `is_featured` is intentionally deferred because the Product schema does not contain that field.

## Branding and favicon

The centralized `SiteConfigurationService` batches database reads for `site.name`, `site.tagline`, `site.logo`, `site.favicon`, `site.phone`, `site.hotline`, `site.email`, `site.address`, `site.working_hours`, `site.copyright`, `site.facebook`, `site.zalo`, and `site.youtube`. Header/footer consume the canonical `site` object; root Blade consumes the favicon value.

```text
HARDCODED_BRAND_IDENTITIES_IN_STOREFRONT_HEADER_FOOTER: 0
HARDCODED_CONTACT_VALUES_IN_STOREFRONT_HEADER_FOOTER: 0
HARDCODED_THEME_COLORS: PRESENT - accepted PR 1 limitation, deferred to PR 2
FAVICON_HTTP_STATUS: 200
FAVICON_CONTENT_TYPE: image/svg+xml
FAVICON_SURVIVED_CONTAINER_RESTART: YES
```

## Authorization

- All 51 `/admin` routes are protected by `auth` and `EnsureUserIsAdmin`.
- Guest redirect, normal-user 403, and admin success are automated.
- Runtime authorization does not grant admin by hardcoded email.

## Verification summary

```text
PHP_TESTS: PASS - 40 tests, 179 assertions
BUILD: PASS - 863 modules
PINT_CHANGED_FILES: PASS - 32 PHP files
MYSQL_MIGRATE: PASS
MYSQL_ROLLBACK: PASS
MYSQL_REMIGRATE: PASS
MYSQL_RELEVANT_TESTS: PASS - 14 tests, 114 assertions
DATA_INTEGRITY: PASS
MANUAL_BROWSER_QA: PASS
NPM_LINT: NOT CONFIGURED
HOMEPAGE_QUERY_COUNT: 19 before, 9 after
```

## Known limitations

- Node 20.15.1 is below Vite 7's declared engine range; production build still passes.
- Plain `npm install` hits the pre-existing Vite 7 / `@vitejs/plugin-vue` 5 peer conflict; committed lock installs with `npm ci --legacy-peer-deps` and no dependency files were changed.
- npm reports 13 existing vulnerabilities; dependency remediation is explicitly deferred.
- Full-repository Pint reports 40 pre-existing style issues outside PR1; all 32 changed PHP files pass Pint. Mass-formatting unrelated files is excluded from closure.
- Non-homepage UTF-8/mojibake debt remains documented in `UTF8-DATA-DEBT.md`.
- Theme/design-token cleanup and other public-page refactors belong to PR 2.
- No GitHub Actions workflow exists in the repository, so CI is not configured.
