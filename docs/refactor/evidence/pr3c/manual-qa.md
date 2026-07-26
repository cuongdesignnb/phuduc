# Manual QA

Environment: local Docker app at `http://localhost:8741`, temporary local admin only, no production database.

| Route | Workflow | Expected | Actual | Console | Overflow | Keyboard/focus | Result |
|---|---|---|---|---|---|---|---|
| `/admin/orders` | Search `ORD-QA-PR3C` | Canonical query updates list without hard reload | URL/query and row updated | Clean | None | Tab reached form control | PASS |
| `/admin/orders/{id}` | `pending -> processing -> shipping -> completed` | Three mutations use fresh server DTO/version | Completed state and timeline updated in one page | Clean | None | Status control/button reachable | PASS |
| `/admin/reviews` | `pending -> approved -> rejected -> approved` | Actions/version refresh after each mutation | Badge/actions updated after each mutation | Clean | None | Filter/actions reachable | PASS |
| `/admin/warranties` | Search/filter and open edit | Canonical filters and DTOs refresh | Row and edit page loaded | Clean | None | Filter/edit controls reachable | PASS |
| `/admin/warranties/{id}/edit` | Update twice without reload | Fresh version/defaults after each save | Both updates committed | Clean | None | Form fields/button reachable | PASS |
| `/admin/warranties` | Void with reason | Row becomes terminal and actions disappear | `Đã hủy`, Edit and Void hidden | Clean | None | Dialog textarea/confirm reachable | PASS |
| `/tra-cuu-bao-hanh` | Manual and order-linked lookup | Safe public result and status | Both modes returned safe result; no void reason | Clean | None | Lookup form reachable | PASS |
| `/admin/warranties/create` | Manual create -> edit, canonical serial, two updates without reload | New id/version applies to form and next save uses PUT | Redirected to edit; `QA-FORM-MANUAL`; v2 and v3 updates committed; no stale-version error | Clean | None | Fields/button reachable | PASS |
| `/admin/warranties/create` | Order-linked create -> change expiry -> save twice without reload | Server snapshot and version remain current | `QA-FORM-ORDER` canonical serial; expiry changed twice; no stale-version error | Clean | None | Picker/select/form reachable | PASS |
| `/admin/orders/{id}` | Resolved cancellation | Success toast only | Status transitioned and success path passed; backend flash contract verified | Clean | None | Status/reason controls reachable | PASS |
| `/admin/orders/{id}` | Unresolved cancellation | Warning toast with exact unresolved count and no success toast | Cancellation and backend Inertia warning contract passed; browser toast was not retained in final tab capture | Clean | None | Status/reason controls reachable | REVIEW |

Responsive matrix: 360x800, 390x844, 768x1024, 1024x768, 1280x900, 1440x1000 and 1920x1080.
All seven warranty edit viewport states had one main landmark, no horizontal overflow and keyboard interaction stayed within the document; final console error/warning count was 0.
Temporary QA admin and fixtures are removed after validation.
