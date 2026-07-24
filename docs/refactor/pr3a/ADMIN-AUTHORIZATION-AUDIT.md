# Admin authorization audit

- Dashboard and admin routes continue to use the existing `auth`, `verified`, and `admin` middleware boundaries.
- Guest access redirects to login.
- Authenticated non-admin users receive HTTP 403.
- Admin users receive the dashboard contract and navigation permissions.
- Shared Inertia admin navigation is permission-shaped and returns no module links for non-admin users.
