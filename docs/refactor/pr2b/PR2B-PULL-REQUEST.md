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

- PHP and contract tests.
- SEO/schema tests.
- Rich-content security tests.
- Vite build.
- Theme audit.
- PR 2B static page audit.
- Changed-scope Pint.

## Known limitations

- Cart, Checkout and utility pages remain for PR 2C.
- Product category schema remains deferred.
- Full production stock/cart validation remains deferred to PR 2C.
- Dependency advisories remain deferred.
- Full-repository Pint remains deferred because existing CheckoutController style is outside PR2B scope.
- ESLint and GitHub Actions are not configured.
- Browser/Lighthouse evidence still needs a longer visual QA pass before marking ready for production deployment.

## Rollback

Revert PR 2B. No destructive database migration is expected.
