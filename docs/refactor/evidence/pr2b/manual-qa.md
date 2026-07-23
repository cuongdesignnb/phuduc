# PR2B Manual QA

Environment:
- Docker QA runtime: http://localhost:8741
- Services: app, db
- Database source: D:\phuduc\phuduc_2026-07-22_171914.sql
- Temporary public QA fixtures used slug prefix `qa-pr2b-`.
- Media files were not restored, so storefront image placeholders/missing-media fallback states were expected during browser QA.

Routes checked:
- `/san-pham`
- `/san-pham/qa-pr2b-forklift-a`
- `/tin-tuc`
- `/tin-tuc/qa-pr2b-article-1`
- `/gioi-thieu`

Screenshots:
- product-index-desktop.png: 1440x1200, 55272 bytes
- product-index-mobile.png: 390x844, 23771 bytes
- product-detail-desktop.png: 1440x1400, 64043 bytes
- product-detail-mobile.png: 390x844, 20485 bytes
- news-index-tablet.png: 768x1024, 41845 bytes
- news-detail-mobile.png: 390x844, 25348 bytes
- about-desktop.png: 1280x900, 44529 bytes

Result:
PASS

Notes:
- Product catalog filters render without layout overlap on desktop/mobile.
- Product detail gallery, 360 control shell, reviews, and rich content render.
- News index/detail and about content render through the canonical `page` prop.
- Temporary QA fixtures and temporary admin user are removed by restoring the SQL dump after QA.
