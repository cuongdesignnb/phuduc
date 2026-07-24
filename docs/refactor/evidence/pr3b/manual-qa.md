# PR3B Manual QA

- Environment: local Docker `app` + isolated MySQL `phuduc_pr3b_test`; production database not used.
- Viewports covered by the QA matrix: 360x800, 390x844, 768x1024, 1024x768, 1280x900, 1440x1000, 1920x1080.
- Product workflow: create/edit, normal image upload, 360 image upload, Media Library attach, delete owned copy, drag reorder.
- Media workflow: search, page navigation, ID hydration, JPEG/PNG WebP conversion, GIF/video/PDF preservation, reference-protected delete.
- Post workflow: category selection, AdvancedTextEditor content, featured media selection, save and stale-version rejection.
- Category workflow: recursive tree rendering, all-level counts, cycle protection, descendant delete guard.
- Menu workflow: recursive four-level tree, drag reorder, keyboard move controls, descendant confirmation, safe URL validation, stale-version rejection.
- Homepage workflow: deterministic section/item fingerprint, remote product/post lookup, selected ID hydration, save refresh.
- Settings workflow: Vietnamese registry labels/descriptions/defaults, canonical `site.og_image`, font options, media picker, max-length and concurrency validation.
- Contract QA: SQLite full suite and isolated MySQL admin suite passed; Vite production build passed; expanded PR3B audit passed with all counters zero.
- Docker is shut down after QA; no production deployment or PR merge was performed.
