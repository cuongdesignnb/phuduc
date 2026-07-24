# PR3A baseline

- Base branch: `main`
- Base SHA: `13193420bf94ac633542e8d097f36f9e3f975253`
- Work branch: `refactor/admin-foundation-dashboard`
- Database policy: isolated SQLite for automated validation; restored local Docker data is never treated as production data.
- Scope: admin foundation, dashboard contract, authorization regression, responsive/accessibility audit, and inventory of existing admin modules.
- Docker policy: compose is started only for QA and validation, then stopped before handoff.

The existing CRUD screens remain inventory items for later phases. PR3A does not change their business workflows.
