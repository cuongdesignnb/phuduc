# PR3B Route Matrix

| Module | Existing | Target |
| --- | --- | --- |
| Media | `GET /admin/media` JSON, POST, DELETE by id | Inertia index, JSON data DTO, upload, PATCH metadata, guarded DELETE |
| Products | resource plus image upload/attach/delete/reorder | resource with request/service contracts and owned media lifecycle |
| Posts | resource | request/service/DTO contracts and Media picker |
| Categories | resource except show | hierarchy request/service/DTO contracts |
| Menus | resource plus `POST menus/{menu}/items` | registry-backed atomic tree sync and version guard |
| Home Content | GET/POST | registry-backed DTO, picker endpoints, fingerprint guard |
| Settings | GET/POST | server registry and atomic batch save |

All mutations remain behind the existing `admin` middleware and additionally use Form Request authorization.
