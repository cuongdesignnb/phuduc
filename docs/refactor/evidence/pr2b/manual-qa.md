# PR2B Manual QA

Environment:
- Docker QA runtime: http://localhost:8741
- Production build: yes, `npm run build` completed and `public/hot` was removed before browser/Lighthouse QA.
- Database source: D:\phuduc\phuduc_2026-07-22_171914.sql
- Temporary public QA fixtures used slug prefix `qa-pr2b-`.
- Media files were not restored, so storefront image placeholders/missing-media fallback states were expected during browser QA.

| Viewport | Product Index | Product Detail | News Index | News Detail | About | Horizontal Overflow | Header | Footer | Focus | Result |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 360x800 | default, filtered, empty state checked | mobile layout checked | checked | checked | checked | none observed | stable | visible | keyboard focus visible | PASS |
| 390x844 | default and filters checked | mobile gallery and review form checked | checked | detail route checked | checked | none observed | stable | visible | keyboard focus visible | PASS |
| 768x1024 | grid/tablet catalog checked | checked | tablet list checked | checked | checked | none observed | stable | visible | keyboard focus visible | PASS |
| 1024x768 | checked | desktop detail, gallery, 360 controls checked | checked | checked | checked | none observed | stable | visible | keyboard focus visible | PASS |
| 1280x900 | checked | checked | checked | checked | desktop about checked | none observed | stable | visible | keyboard focus visible | PASS |
| 1440x1000 | desktop catalog checked | desktop detail checked | checked | checked | checked | none observed | stable | visible | keyboard focus visible | PASS |
| 1920x1080 | checked | checked | checked | checked | wide about checked | none observed | stable | visible | keyboard focus visible | PASS |

Screenshots:
- product-index-360.png: 360x800, 23127 bytes
- product-index-390.png: 390x844, 25611 bytes
- news-index-768.png: 768x1024, 38158 bytes
- product-detail-1024.png: 1024x768, 50561 bytes
- about-1280.png: 1280x900, 44402 bytes
- product-detail-1440.png: 1440x1000, 62538 bytes
- about-1920.png: 1920x1080, 54363 bytes

Scenarios:
- Product Index: default, filtered, empty.
- Product Detail: desktop, mobile, gallery, 360 control, review form.
- News: index, filtered, detail.
- About: desktop, mobile.

Cleanup:
- QA_FIXTURES_REMOVED=YES
- TEMP_ADMIN_REMOVED=YES
- DATABASE_RESTORED_AFTER_QA=YES
- PRODUCTION_DATABASE_USED=NO
