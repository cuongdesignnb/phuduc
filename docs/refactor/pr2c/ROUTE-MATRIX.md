# Route Matrix

| Name | Method | URI | Middleware/behavior |
| --- | --- | --- | --- |
| `cart.index` | GET | `/gio-hang` | utility headers, canonical cart read |
| `cart.add` | POST | `/gio-hang/add` | bounded product mutation |
| `cart.update` | PATCH | `/gio-hang/update` | bounded quantity mutation |
| `cart.remove` | DELETE | `/gio-hang/remove` | idempotent mutation |
| `cart.clear` | POST | `/gio-hang/clear` | explicit clear |
| `checkout.index` | GET | `/thanh-toan` | creates opaque intent, utility headers |
| `checkout.store` | POST | `/thanh-toan` | intent required, atomic idempotent checkout |
| `checkout.success` | GET | `/thanh-toan/thanh-cong/{token}` | opaque token, 404 on invalid |
| `order-lookup.index` | GET | `/tra-cuu-don-hang` | form, noindex/no-store |
| `order-lookup.lookup` | POST | `/tra-cuu-don-hang` | two-factor, rate limited, generic failure |
| `warranty-lookup.index` | GET | `/tra-cuu-bao-hanh` | form, noindex/no-store |
| `warranty-lookup.lookup` | POST | `/tra-cuu-bao-hanh` | two-factor, rate limited, generic failure |
