# PR3B Accessibility Review

- All required viewport widths were included in the manual QA matrix.
- Admin dialogs retain `role="dialog"`, `aria-modal`, labelled headings, Escape handling, focus containment, and focus return.
- Media, menu, category, product-image and destructive-action controls have text labels or accessible names.
- Menu reorder controls expose keyboard labels for move up, move down, indent and outdent; drag handles remain available for pointer users.
- Recursive Menu and Category components keep heading/content hierarchy without hidden one-level-only controls.
- Rich editor media insertion uses the shared Media picker and media URLs supplied by the server.
- Validation and concurrency errors are rendered through the shared admin error summary.
- No direct storage paths, native confirmation dialogs, hash-only controls, or legacy Carbon/Volt/Industrial tokens remain in the PR3B audit scope.
- Automated evidence: `php artisan test` passed on SQLite; the isolated MySQL PR3B admin suite passed; Vite build and expanded audit passed.
