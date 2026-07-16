# PR 1 manual QA

Date: 2026-07-16
Environment: Docker local (`http://localhost:8741`, MySQL host port `3641`)
Browser: Codex in-app browser, desktop and mobile viewports

## Storefront

- `/` loaded successfully on desktop and mobile with no console error in a fresh tab.
- Canonical `site / navigation / page.sections` data rendered in registry order.
- Empty item collections did not render fake cards, mock data, or auto-filled products.
- Branding logo saved through Admin appeared in both storefront header and footer.

## Admin

- `/admin/home-content` displayed all 10 registry sections in the single homepage editor.
- Hero schema fields, section tabs, variants, enable/disable controls, and drag reorder UI rendered.
- Featured Products manual source displayed the product picker; the temporary UI selection was not persisted.
- `/admin/settings` saved logo and favicon values successfully.
- Guest access redirects to login; normal-user access returns 403; admin access succeeds (also covered by automated tests).

## Favicon proof

```text
SETTING_VALUE: pr1-closure-favicon.svg
RENDERED_HREF: http://localhost:8741/storage/pr1-closure-favicon.svg
HTTP_STATUS: 200
CONTENT_TYPE: image/svg+xml
CONTENT_LENGTH: 238
AFTER_CONTAINER_RESTART: same href, HTTP 200
```

The browser security policy blocks `view-source:` navigation. Verification therefore used the root Blade-rendered `<head>` in a fresh page after container restart, inspected both `rel="icon"` and `rel="shortcut icon"`, and independently requested the referenced asset to confirm status and MIME type. The temporary setting values, QA admin user, and SVG asset were removed after evidence capture.

## Screenshots

- `screenshots/homepage-desktop.png`
- `screenshots/homepage-mobile.png`
- `screenshots/admin-section-tabs.png`
- `screenshots/hero-editor.png`
- `screenshots/featured-products-manual-picker.png`
- `screenshots/section-reorder.png`
- `screenshots/branding-logo.png`
- `screenshots/favicon-setting.png`
- `screenshots/favicon-http-asset.png`

## Cleanup verification

- `site.logo` restored to its original empty value.
- `site.favicon` restored to its original null value.
- Temporary `codex-pr1-qa@local.test` user deleted.
- Temporary `storage/app/public/pr1-closure-favicon.svg` deleted.
