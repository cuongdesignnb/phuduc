# PR2B Component Migration Matrix

## Existing Shared Components To Reuse

- `GuestPageLayout`
- `SeoHead`
- `StorefrontContainer`
- `PageHero`
- `Breadcrumbs`
- `ProductCard`
- `NewsCard`
- `Pagination`
- `ResponsiveImage`
- `QuantityStepper`
- `FormField`
- `UiButton`
- `UiCard`
- `SectionHeader`
- `StatusBadge`
- `EmptyState`
- `StorefrontIcon`

## Components To Add Or Refactor

- `ProductCatalogFilters`
- `ProductGallery`
- `ProductReviewSummary`
- `ProductReviewList`
- `ProductReviewForm`
- `RichContent`
- `ProductViewer360`

## Migration Notes

- Product and news grids should use shared cards.
- Rich HTML should only render through `RichContent`.
- Product gallery receives normalized URLs, not image paths.
- Product 360 receives `frames: [{ id, url, alt }]`.
- Pagination receives normalized links and must not create disabled anchors.
- Product and news filters submit intentionally, not after every keystroke.
