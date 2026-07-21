# UTF-8 Data Debt

## Current state

Source and seeded legacy content contain mojibake from earlier encoding paths. Several out-of-scope Admin/product/news components also contain local `TextDecoder` compatibility logic. This PR removes duplicated fixers from the homepage, Home Content Admin, Settings, and Header/Footer paths and does not add a new fixer.

New homepage defaults and contract code are stored as UTF-8. HTML already declares `<meta charset="utf-8">`.

## Production audit before repair

Before a separate repair PR, record:

- MySQL server/database/table charset and collation (`utf8mb4` expected).
- Connection variables `character_set_client`, `character_set_connection`, `character_set_results`.
- Column collations for settings, products, posts, menus and homepage content.
- HTTP `Content-Type` charset and HTML meta charset.
- A byte-level sample of good and broken rows to distinguish UTF-8 text from double-encoded text.

## Guardrails

- Do not run automatic production conversion in this PR.
- Back up affected tables before any repair.
- Repair only rows proven to be double encoded; make the repair idempotent and reversible.
- Remove remaining component-level decoders only after data is repaired and verified.
- Add round-trip tests for Vietnamese input through Admin → DB → Inertia → rendered HTML.
