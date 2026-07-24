# Page Contracts

All five PR2C utility pages use `GuestPageLayout` and backend-owned display strings.

| Page | Props | Privacy |
| --- | --- | --- |
| Cart | `page`, `warnings`, `items`, `summary`, `seo` | noindex/no-store |
| Checkout | `checkout_intent`, `items`, `summary`, `seo` | noindex/no-store |
| Success | `order` safe DTO, `seo` | noindex/no-store |
| Order lookup | `searched`, `order` safe DTO or null, `seo` | POST result no-store |
| Warranty lookup | `searched`, `warranty` safe DTO or null, `seo` | POST result no-store |

No raw Eloquent model crosses the Inertia boundary. Money and dates are preformatted by backend presenters. No PII is included in URLs, SEO metadata, JSON-LD, logs, or evidence.
