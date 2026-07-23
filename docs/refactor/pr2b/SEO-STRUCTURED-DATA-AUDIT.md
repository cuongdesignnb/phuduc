# PR2B SEO And Structured Data Audit

## Current Risks

- `SeoService` queries settings directly in static methods.
- Product and article schemas build direct `storage/` URLs.
- Product aggregate rating can be computed from a limited review collection.
- Organization schema emits empty nested fields.
- Breadcrumb JSON-LD is assembled separately from UI breadcrumb data.
- Product index filtered URLs do not adjust robots or canonical behavior.
- Article schema can output empty image or publisher logo data.

## Required Direction

- Add `StorefrontSeoService` using canonical `SiteConfigurationService` and `MediaUrlService`.
- Build UI breadcrumbs once and derive JSON-LD from the same array.
- Product schema uses normalized product/media data and full review aggregate.
- Article schema uses normalized post/media data.
- Organization schema omits empty optional fields.
- Product index with filters uses `noindex, follow` and canonical `/san-pham`.
- Unfiltered product index uses `index, follow` and canonical `/san-pham`.
- News search pages use `noindex, follow`.
