## Summary

Unify the product catalog, product detail, news and About pages on the canonical storefront data and design systems, with final BA review fixes for Vietnamese content, accessibility, query isolation, SEO schema and rich-content URL security.

## Major Changes

- Canonical Product Index and Product Detail contracts.
- Canonical News Index and News Detail contracts.
- Canonical About page contract.
- Shared product/news cards, pagination, filters and rich-content rendering.
- URL-synchronized filters with visible validation errors for invalid price ranges.
- Deterministic related products and posts.
- Centralized media, price and date presentation.
- Correct review aggregates.
- SEO and structured-data normalization.
- Product 360 viewer and Product Gallery accessibility improvements.
- UTF-8, unaccented-label and hardcoded-path static audits.

## Final BA Fixes

- Restored fully accented Vietnamese labels in public storefront components.
- Added screen-reader labels for Product Gallery, Pagination and Breadcrumbs.
- Ensured Product Detail and other storefront pages keep exactly one H1.
- Rendered Product Index min/max price validation errors without redirect loops.
- Preserved invalid filter values and focused the first errored field.
- Rebalanced query-count tests with per-scenario warm-up and cache isolation.
- Added About meta description fallback and conditional Organization contact/address schema.
- Hid empty About contact section when no contact data exists.
- Hardened RichHtmlSanitizer URL allowlists against obfuscated JavaScript, data, file and custom schemes.

## Validation

- PHP suite: 152 passed, 941 assertions.
- Final PR2B contract/security/query slice: 45 passed, 284 assertions.
- Product query counts: 3 / 3.
- Product detail query counts: 5 / 5.
- News index query counts: 4 / 4.
- News detail query counts: 4 / 4.
- About query count: 1.
- Vite production build: PASS.
- Storefront theme audit: PASS.
- PR2B page audit: PASS, 48 files scanned, unaccented public label hits 0.
- Changed-scope Pint: PASS, 34 files.
- git diff check: PASS.

## Lighthouse

- Method: 3 simulated mobile runs per route, 1 desktop run per route, Lighthouse 12.8.2.
- Product Index mobile median: 62, desktop: 99.
- Product Detail mobile median: 62, desktop: 99.
- News Index mobile median: 62, desktop: 99.
- News Detail mobile median: 62, desktop: 99.
- About mobile median: 62, desktop: 99.
- Accessibility minimum: 95.
- Best Practices minimum: 96.
- SEO minimum: 100.
- Maximum CLS: 0.002.
- Raw Lighthouse reports are not committed.
- Production compressed retest remains required.

## Evidence

- `docs/refactor/evidence/pr2b/php-tests.txt`
- `docs/refactor/evidence/pr2b/contract-tests.txt`
- `docs/refactor/evidence/pr2b/security-tests.txt`
- `docs/refactor/evidence/pr2b/query-counts.txt`
- `docs/refactor/evidence/pr2b/query-counts-base.txt`
- `docs/refactor/evidence/pr2b/query-counts-head.txt`
- `docs/refactor/evidence/pr2b/lighthouse-summary.json`
- `docs/refactor/evidence/pr2b/manual-qa.md`
- `docs/refactor/evidence/pr2b/pr2b-page-audit.txt`
- `docs/refactor/evidence/pr2b/seo-schema-validation.md`
- `docs/refactor/evidence/pr2b/screenshots/product-index-invalid-price.png`

## Known Limitations

- Cart, Checkout and utility pages remain for PR 2C.
- Product category schema remains deferred.
- Full production stock/cart validation remains deferred to PR 2C.
- Dependency advisories remain deferred.
- ESLint and GitHub Actions are not configured.
- Docker QA used temporary local fixtures because the restored SQL dump contains no products/posts and media files were not restored.
- Production compressed retest remains required; local Docker QA is not compressed production hosting.

## Rollback

Revert PR 2B. No destructive database migration is expected.
