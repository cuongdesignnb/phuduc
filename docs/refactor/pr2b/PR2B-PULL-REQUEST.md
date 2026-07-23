## Summary

Unify the product catalog, product detail, news and About pages on the canonical storefront data and design systems.

## Major changes

- Canonical Product Index and Product Detail contracts.
- Canonical News Index and News Detail contracts.
- Canonical About page contract.
- Shared product/news cards, pagination, filters and rich-content rendering.
- URL-synchronized filters.
- Deterministic related products and posts.
- Centralized media, price and date presentation.
- Correct review aggregates.
- SEO and structured-data normalization.
- Product 360 viewer accessibility improvements.
- UTF-8 and hardcoded-path static audits.

## Functional decisions

- No product category filter is introduced because Product has no category relationship.
- Related products use deterministic newest-active fallback.
- Related posts use the same category and are not randomly filled.
- Cart and Checkout business logic remain deferred to PR 2C.
- No fake products, specifications, reviews, statistics or About content.

## Testing

- PHP suite: 120 passed, 804 assertions.
- Contract/BA review slice: 15 passed, 152 assertions.
- SEO/schema tests.
- Rich-content security tests.
- Vite production build.
- Theme audit.
- PR 2B static page audit with unaccented Vietnamese label checks.
- Changed-scope Pint.
- Request-only query-count base/head evidence.
- Browser screenshot QA across all 7 required viewports.
- Lighthouse summary with 3 simulated mobile runs per route and 1 desktop run per route.

## BA review fixes

- Restored fully accented Vietnamese storefront content.
- Added Vietnamese contract and price-display regression tests.
- Corrected query-count measurement to exclude fixture setup queries.
- Added collection-size scaling assertions for N+1 protection.
- Re-ran Lighthouse with simulated mobile profiles, medians and Web Vitals.
- Completed evidence across all seven required viewports.
- Invalid news category slugs now return 404.

## Known limitations

- Cart, Checkout and utility pages remain for PR 2C.
- Product category schema remains deferred.
- Full production stock/cart validation remains deferred to PR 2C.
- Dependency advisories remain deferred.
- ESLint and GitHub Actions are not configured.
- Docker QA used temporary local fixtures because the restored SQL dump contains no products/posts and media files were not restored.
- Production compressed retest remains required; local Docker QA is not compressed production hosting.

## Rollback

Revert PR 2B. No destructive database migration is expected.
