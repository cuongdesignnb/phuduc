# PR3C Baseline

## Repository

- Repository: `cuongdesignnb/phuduc`
- Base branch: `main`
- Base SHA: `077b60dd46102b0ccd99108cc5336089d917b2ce`
- Work branch: `refactor/admin-orders-reviews-warranties`
- Baseline captured before PR3C code changes.

## Runtime

- QA runtime: Docker Compose `app` and `db` services.
- Database used for the baseline suite: isolated in-memory SQLite.
- Production database and production data: not used.

## Results

- SQLite Laravel suite: 279 passed, 1 skipped, 2256 assertions.
- Vite build: PASS, 910 modules transformed.
- Storefront theme audit: PASS.
- PR2B pages/evidence audits: PASS.
- PR2C commerce audit: PASS.
- PR3A admin audit: PASS.
- PR3B admin-content audit: PASS.
- `git diff --check`: PASS.

## Scope note

This document records the pre-implementation state. Docker remains a QA-only runtime and must be stopped after validation.
