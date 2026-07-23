# PR2C Baseline

Baseline captured on 2026-07-23 from `refactor/storefront-commerce-utility-pages` at base SHA `ac12007861530cdb08cd7f44ccf6babfc103b5a4`.

- Docker services: `app`, `db`; MySQL was healthy during the run.
- PHP: 161 passed, 1215 assertions.
- Vite build: pass.
- `audit:storefront-theme`: pass, 49 files, 13 forbidden patterns.
- `audit:pr2b-pages`: pass, 49 files scanned; all reported hits are zero.
- `audit:pr2b-evidence`: pass, 22 files scanned; UTF-8 labels passed.
- Docker was stopped after baseline validation; no containers remain running.

The baseline application has PR2C findings documented below: session cart entries contain product presentation and price, checkout trusts session values, success uses numeric order route binding, lookup results expose a raw model, warranty lookup uses one factor, and utility pages use default index/cache behavior.
