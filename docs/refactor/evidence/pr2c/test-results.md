# PR2C Test Results

Date: 2026-07-24

- Full SQLite test suite: 191 passed, 1 skipped, 1,647 assertions. The MySQL-only unique-conflict case is skipped under SQLite.
- Isolated MySQL targeted suite: 16 passed, 193 assertions. This covers intent idempotency/lifecycle, unique-conflict recovery, stock integrity, privacy success, mutation limits, phone normalization, and lookup contracts.
- `npm run audit:storefront-theme`: PASS.
- `npm run audit:pr2b-pages`: PASS.
- `npm run audit:pr2b-evidence`: PASS.
- `npm run audit:pr2c-commerce`: PASS; all forbidden-pattern counters are zero.
- Container `npm run build`: PASS, Vite 887 modules.
- Pint on changed PHP files: PASS.
- Lighthouse 12.8.2: PASS_WITH_NOINDEX_EXCEPTION for four utility routes, 3 simulated-mobile runs and 1 desktop run per route; checkout stayed on `/thanh-toan` with zero redirects. Production compressed retest is required.

Host `npm run build` was not usable because the host checkout does not contain the container's Composer vendor dependencies; the authoritative production build ran inside the app container.
