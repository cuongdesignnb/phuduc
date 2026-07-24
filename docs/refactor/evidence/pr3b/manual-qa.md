# PR3B Manual QA

- Environment: Docker `app` plus isolated MySQL `phuduc_pr3b_test`; production database was not used.
- Viewport 360x800: product image controls, media picker, menu tree and form actions fit without horizontal overflow.
- Viewport 390x844: image clear actions and menu target search remain reachable and labels stay inside controls.
- Viewport 768x1024: two-column admin forms and recursive menu controls remain readable.
- Viewport 1024x768: catalog, post, settings and homepage workflows retain their primary actions.
- Viewport 1280x900: admin tables, pickers and menu structure support repeated editing without overlap.
- Viewport 1440x1000: content editing, keyboard reorder and remote lookup states remain visible.
- Viewport 1920x1080: full admin shell and dense content forms preserve scan order and focus states.
- Product workflow: create/edit, normal image upload, 360 image upload, image-only Media Library attach, clear, delete owned copy, drag reorder and keyboard reorder.
- Media workflow: image filter, search, page navigation, selected ID hydration, JPEG/PNG WebP conversion, GIF/video/PDF preservation and reference-protected delete.
- Post workflow: category selection, rich HTML editor, image-only featured media, clear action, save and stale-version rejection.
- Category workflow: recursive tree rendering, all-level counts, cycle protection and descendant delete guard.
- Menu workflow: product/post/category target lookup, existing ID hydration, URL target clearing, recursive four-level tree, drag reorder, keyboard moves, descendant confirmation, safe HTTP/HTTPS/mailto/tel validation and stale-version rejection.
- Homepage workflow: deterministic section/item fingerprint, remote product/post lookup, image ID hydration, image clear action and save refresh.
- Settings workflow: Vietnamese registry labels/descriptions/defaults, canonical `site.og_image`, font options, image-only picker, clear action, max-length and concurrency validation.
- Contract QA: SQLite `245 passed, 1 skipped`, isolated MySQL admin suite `74 passed`, Vite production build `910 modules`, Pint and expanded PR3B audit passed.
- Docker was shut down after QA; no production deployment, merge or PR3C work was performed.
