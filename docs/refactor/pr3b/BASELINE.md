# PR3B Baseline

## Repository

- Base branch: `main`
- Base SHA: `545bf1502456b1adc4490990441c2b98d1101e83`
- Work branch: `refactor/admin-catalog-content-modules`
- Production deployment: no

## Validation before implementation

- SQLite suite: 207 passed, 1 skipped, 1785 assertions
- Vite build: pass
- Storefront theme audit: pass
- PR2B pages audit: pass
- PR2B evidence audit: pass
- PR2C commerce audit: pass
- PR3A admin audit: pass
- Git diff check: pass

## Known PR3B gaps

- Media index currently returns a JSON paginator instead of an Inertia page.
- Media upload uses a single-file request, dispatches a path-mutating conversion job, and deletion has no reference guard.
- Product index eager-loads all images, returns raw paginator/models, formats money in Vue, and uses direct storage URLs.
- Product image attach stores the Media path directly and image deletion removes the physical file without ownership checks.
- Product and Post controllers contain validation, slug generation, persistence, storage, and presentation responsibilities.
- Post and category pages return raw Eloquent collections and category hierarchy mutation has no cycle or delete guard.
- Menu save deletes and recreates the complete item tree, accepts arbitrary model types, and has no transaction, ownership, or version contract.
- Home Content still exposes broad lookup collections and accepts media paths instead of stable media references.
- Settings accepts client-provided type values and saves each setting independently without a registry or atomic batch contract.
- Existing PR3B Vue pages contain legacy admin tokens, client formatting, direct `/storage/` paths, text repair code, and native `confirm` calls.

## Scope boundary

Orders, Reviews, Warranties, authentication, roles/RBAC, storefront commerce, payments, Kiot integration, and production deployment remain out of scope for PR3B.
