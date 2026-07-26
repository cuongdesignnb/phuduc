# PR3C Privacy Matrix

| Surface | Allowed | Forbidden |
| --- | --- | --- |
| Public order lookup | Order number, status label, dates, item snapshots, total | Email, full phone, shipping address, notes, IDs, tokens, history |
| Public warranty lookup | Product snapshot, serial, dates, effective status | Phone, order ID, customer contacts, void reason, internal IDs |
| Admin order index | Operational customer name/phone, order number, status, total, created display, URLs | Checkout intent, public token, raw model attributes |
| Admin order detail | Explicit operational customer/order/item/history DTO fields | Tokens, raw models, unnecessary internal metadata |
| Admin review index | Reviewer display name, product name, rating, content, status, date, allowed actions | Full phone/email, raw model attributes |
| Admin warranty index | Serial, product snapshot, masked phone, source, order number, dates, effective status, actions | Raw order/warranty models, void reason unless detail policy permits |

Every Inertia response must use explicit DTO arrays and bounded relations. Public failures remain generic so they do not reveal whether a serial, order or phone exists.
