# Admin Catalog and Content Inventory

## Modules

Products, Product Images, Media Library, Posts, Post Categories, Menus, Menu Items, Home Content, and Settings.

## Existing ownership

- Controllers: `app/Http/Controllers/Admin`
- Requests: only `SaveHomeContentRequest` exists for the PR3B modules.
- Models: `Product`, `ProductImage`, `MediaLibrary`, `Post`, `PostCategory`, `Menu`, `MenuItem`, `HomeSection`, `HomeSectionItem`, `Setting`.
- Shared media URL resolver: `app/Services/Storefront/MediaUrlService.php`.
- Home registry: `app/Support/Homepage/HomeSectionRegistry.php`.
- Frontend pages: `resources/js/Pages/Admin`.

## Required target ownership

Controllers receive Form Requests, invoke Admin services, and return canonical Inertia envelopes or redirects. Presentation services own all labels, dates, money, media URLs, pagination, reference counts, and action URLs.
