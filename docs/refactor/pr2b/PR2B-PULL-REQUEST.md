## Summary

Unify the product catalog, product detail, news and About pages on the canonical storefront data and design systems, with final BA review fixes for Vietnamese content, accessibility, query isolation, SEO schema, rich-content URL security and the final product filter validation flow.

## Major Changes

- Canonical Product Index and Product Detail contracts.
- Canonical News Index and News Detail contracts.
- Canonical About page contract.
- Shared product/news cards, pagination, filters and rich-content rendering.
- URL-synchronized filters with visible validation errors for invalid price ranges.
- Single-source Product Catalog filter resolver separating display filters from safe query filters.
- Deterministic related products and posts.
- Centralized media, price and date presentation.
- Correct review aggregates.
- SEO and structured-data normalization.
- Product 360 viewer and Product Gallery accessibility improvements.
- UTF-8, unaccented-label, hardcoded-path and filter-architecture static audits.

## Final BA Fixes

- Removed duplicate filter rules from ProductController and deleted the unused ProductCatalogRequest.
- Added ProductCatalogFilterResolver as the only validation/normalization source.
- Split raw display filters from safe query filters so invalid values never reach the query builder.
- Preserved raw invalid values so users can correct them.
- Focus now moves to the first invalid field in DOM order: search, min price, max price, sort.
- Error summary now uses role=alert and aria-live=polite.
- Restored fully accented Vietnamese labels in public storefront components.
- Added screen-reader labels for Product Gallery, Pagination and Breadcrumbs.
- Ensured Product Detail and other storefront pages keep exactly one H1.
- Added About meta description fallback and conditional Organization contact/address schema.
- Hardened RichHtmlSanitizer URL allowlists against obfuscated JavaScript, data, file and custom schemes.

## Validation

- PHP suite: 158 passed, 1018 assertions.
- Product filter contract slice: 9 passed, 132 assertions.
- Product query counts: 3 / 3.
- Product detail query counts: 5 / 5.
- News index query counts: 4 / 4.
- News detail query counts: 4 / 4.
- About query count: 1.
- Vite production build: PASS.
- Storefront theme audit: PASS.
- PR2B page audit: PASS, 49 files scanned.
- ProductController Validator::make hits: 0.
- Duplicate product filter rulesets: 0.
- Unused ProductCatalogRequest: 0.
- Pint: PASS, 28 files.
- git diff check: PASS.

## Filter QA

- Long search excluded from query: PASS.
- Negative min price excluded: PASS.
- Negative max price excluded: PASS.
- Oversized price excluded: PASS.
- Invalid range excludes both price filters: PASS.
- Invalid sort falls back to latest query sort: PASS.
- Raw invalid values preserved: PASS.
- Correct error field focused at 360px and 1024px: PASS.
- aria-invalid and aria-describedby per invalid field: PASS.

## Lighthouse

Existing Lighthouse evidence is retained because this round only changes validation architecture and focus behavior.

- Product Index mobile median: 62, desktop: 99.
- Product Detail mobile median: 62, desktop: 99.
- News Index mobile median: 62, desktop: 99.
- News Detail mobile median: 62, desktop: 99.
- About mobile median: 62, desktop: 99.
- Accessibility minimum: 95.
- Best Practices minimum: 96.
- SEO minimum: 100.
- Maximum CLS: 0.002.
- Production compressed retest remains required.

## Evidence

- `docs/refactor/evidence/pr2b/php-tests.txt`
- `docs/refactor/evidence/pr2b/contract-tests.txt`
- `docs/refactor/evidence/pr2b/manual-qa.md`
- `docs/refactor/evidence/pr2b/pr2b-page-audit.txt`
- `docs/refactor/evidence/pr2b/pint.txt`
- `docs/refactor/evidence/pr2b/lighthouse-summary.json`

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
