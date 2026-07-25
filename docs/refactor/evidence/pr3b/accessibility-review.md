# PR3B Accessibility Review

- Viewports reviewed: 360x800, 390x844, 768x1024, 1024x768, 1280x900, 1440x1000 and 1920x1080.
- Dialogs retain `role="dialog"`, `aria-modal`, labelled headings, Escape handling, focus containment and focus return.
- Media, menu, category, product-image and destructive-action controls have visible labels or accessible names.
- Menu reorder controls expose keyboard labels for move up, move down, indent and outdent; drag handles remain available for pointer users.
- Recursive Menu and Category components keep heading/content hierarchy without hidden one-level-only controls.
- Rich editor media insertion uses the shared image-only picker and server-supplied media URLs.
- Validation and concurrency errors are rendered through the shared admin error summary.
- No direct storage paths, native confirmation dialogs, hash-only controls or legacy Carbon/Volt/Industrial tokens remain in the PR3B audit scope.
- Automated evidence: SQLite `245 passed, 1 skipped`, MySQL admin `74 passed`, Vite `910 modules`, Pint PASS and expanded audit counters all zero.
