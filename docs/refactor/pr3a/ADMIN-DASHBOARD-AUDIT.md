# Admin dashboard audit

- Controller delegates to `AdminDashboardService` and contains no dashboard queries.
- Presentation is server-owned: money, dates, labels, URLs, and bounded DTOs are prepared before Inertia serialization.
- Revenue aggregation uses at most six months of rows and PHP month buckets, with zero-filled months.
- Recent order DTOs select only operational display fields and omit checkout tokens, address, email, phone, notes, and raw model fields.
- Recent review DTOs omit review content and customer contact fields.
- Top products are bounded to five records and expose display-safe fields only.
- Automated query budget is capped at 12 dashboard service queries.
