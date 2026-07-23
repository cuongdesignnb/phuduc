## Summary

Unify the product catalog, product detail, news and About pages on the canonical storefront data and design systems, with final BA closure fixes for UTF-8 evidence and Vietnamese Product Catalog validation messages.

## Major Changes

- Canonical Product Index and Product Detail contracts.
- Canonical News Index and News Detail contracts.
- Canonical About page contract.
- Shared product/news cards, pagination, filters and rich-content rendering.
- URL-synchronized filters with visible validation errors for invalid price ranges.
- Single-source Product Catalog filter resolver separating display filters from safe query filters.
- Product Catalog validation messages are Vietnamese without relying on APP_LOCALE.
- Deterministic related products and posts.
- Centralized media, price and date presentation.
- Correct review aggregates.
- SEO and structured-data normalization.
- UTF-8, unaccented-label, evidence-encoding, hardcoded-path and filter-architecture static audits.

## Final Closure Fixes

- Repaired PR2B evidence mojibake in manual QA and UTF-8 audit evidence.
- Added `audit:pr2b-evidence` to scan PR2B evidence and PR markdown files.
- Added mandatory accented-label checks for PR2B evidence.
- Added resolver-level Vietnamese validation messages and attributes.
- Verified English filter validation hits are 0.
- Preserved invalid raw values while excluding invalid values from query filters.
- Retested focus behavior at 360px and 1024px.

## Validation

- PHP suite: 161 passed, 1215 assertions.
- Product filter contract slice: 12 passed, 329 assertions.
- Evidence UTF-8 tests: PASS.
- Product query counts: 3 / 3.
- Product detail query counts: 5 / 5.
- News index query counts: 4 / 4.
- News detail query counts: 4 / 4.
- About query count: 1.
- Vite production build: PASS.
- Storefront theme audit: PASS.
- PR2B page audit: PASS, 49 source files scanned.
- PR2B evidence audit: PASS, 22 evidence files scanned.
- Evidence mojibake hits: 0.
- Evidence mandatory labels: PASS.
- Product filter validation language: Vietnamese.
- English filter validation hits: 0.
- Pint: PASS, 2 files.
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
- Error messages are Vietnamese in browser QA: PASS.

## Lighthouse

Existing Lighthouse evidence is retained because this round only changes validation text and evidence encoding.

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
- `docs/refactor/evidence/pr2b/utf8-audit.txt`
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
