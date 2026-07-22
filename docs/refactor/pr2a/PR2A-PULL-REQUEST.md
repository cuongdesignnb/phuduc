## Summary

Establish one dynamic design system for the public storefront and migrate the global shell and homepage to it.

## Major changes

- Canonical server-generated theme tokens.
- No-flash initial theme bootstrap.
- Single runtime theme application.
- Tailwind CSS variable token mapping.
- Shared storefront component primitives.
- Functional desktop/mobile search.
- Accessible multi-level navigation.
- Responsive Header/Footer.
- Homepage section component migration.
- Real section variants.
- Static hardcoded-theme audit.

## Functional decisions

- Removed non-functional category controls.
- Removed non-functional newsletter form.
- Guest/Admin/non-Admin account destinations are explicit.
- Product and news cards consume canonical data contracts.

## BA review fixes

- Added accessible focus and control-boundary tokens for light primary colors.
- Added mobile drawer focus trap and focus restoration.
- Disabled link-style buttons no longer navigate by mouse or keyboard.
- Removed intermediate Lighthouse raw reports.
- Added backend/runtime theme-token parity enforcement.

## Testing

- PHP: 95 tests, 520 assertions.
- Theme token/bootstrap/contract: 34 tests, 175 assertions.
- Runtime parity: 2 tests, 52 assertions.
- Vite production build: pass (874 modules).
- Static storefront theme audit: pass (43 files, 13 forbidden patterns).
- Changed-scope Pint: pass (3 files).
- Responsive browser QA: 360, 390, 768, 1024, 1280, 1440 and 1920 px.
- Lighthouse median mobile: Performance 68, Accessibility 100, Best Practices 100, SEO 100, CLS 0.0001.
- Lighthouse median desktop: Performance 94, Accessibility 100, Best Practices 100, SEO 100, CLS 0.0007.

## Known limitations

- Product/content pages are migrated in PR 2B.
- Commerce/utility pages are migrated in PR 2C.
- Mobile Lighthouse Performance is below the local target on the PHP development server, which serves production assets without HTTP compression; the median is reported from three runs without score manipulation.
- Dependency advisories remain deferred.
- Existing full-repository Pint baseline remains deferred.
- GitHub Actions is not configured.
- ESLint is not configured; static theme audit is used instead.
- The host Node.js version is 20.15.1 while Vite recommends 20.19+ or 22.12+; the production build still passes.
- The host PHP installation emits optional OCI/Firebird extension warnings; the test suite still passes.

## Rollback

Revert PR 2A. No destructive business-data migration is expected.
