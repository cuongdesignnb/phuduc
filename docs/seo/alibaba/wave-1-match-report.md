# Alibaba image search & product-spec verification — Wave 1

## Status

STATUS=ALIBABA_CAPTCHA_BLOCKED

On 2026-09-02, the Phú Đức WebP research inputs were submitted directly to Alibaba Image Search through the visible Upload control and its browser file-chooser handler; no native Windows file picker was used.

Three searches returned result pages before Alibaba displayed a slider CAPTCHA on the fourth submission. The CAPTCHA is a site security control, so it was not solved or bypassed. No candidate listing page was opened after that point, and no supplier specification is treated as verified.

## Measured run evidence

| Metric | Value |
| --- | ---: |
| Local-image submissions accepted by the browser control | 4 |
| Result pages returned | 3 |
| Result-card sets inspected | 3 |
| Candidate listing pages opened | 0 |
| Alibaba-verified specification fields | 0 |
| Content drafts changed | 0 |

The returned pages contained at least 20 cards for Cẩu điện thủy lực 2 tấn primary image and 12 visible cards for Cần cẩu chân nhện primary image. The hydraulic-crane detail image returned unrelated component/automotive results and is not evidence of a product-family match.

## Wave summary

| Product | Phú Đức URL | Alibaba best match | Visual score | Match level | Alibaba-verified fields | Conflicts / risks | Action |
| --- | --- | --- | --- | --- | ---: | --- | --- |
| Cẩu điện thủy lực 2 tấn | <https://phuduc.com/san-pham/cau-dien-thuy-luc-2-tan> | Search-card shortlist only; listing not opened | Not scored | C | 0 | Product photo visibly says 1 tấn; candidate titles include both 1 tấn and 2 tấn claims | MANUAL_REVIEW |
| Cần cẩu chân nhện | <https://phuduc.com/san-pham/can-cau-chan-nhen> | Search-card shortlist only; listing not opened | Not scored | C | 0 | Gallery is not mapped to 1/5/8/12-ton variants | MANUAL_REVIEW |
| Cẩu trục chạy điện có chân di chuyển | <https://phuduc.com/san-pham/cau-truc-chay-dien-co-chan-di-chuyen> | Not run after CAPTCHA | — | C | 0 | Different visible gantry configurations; no variant mapping | MANUAL_REVIEW |
| Máy xúc lật 4 bánh gầu 0,4 m³ | <https://phuduc.com/san-pham/may-xuc-lat-4-banh-gau-0-4-m3> | Not run after CAPTCHA | — | C | 0 | Electric/diesel images are not mapped to variants | MANUAL_REVIEW |
| Xe nâng điện | <https://phuduc.com/san-pham/xe-nang-dien> | Not run after CAPTCHA | — | C | 0 | Several apparent model marks; no mapping to 1/1.5/2.5/3.5-ton variants | MANUAL_REVIEW |

## Guardrails applied

- No Alibaba price, warranty, origin, delivery, certification, or supplier claim was copied into Phú Đức content.
- No Alibaba research image was saved because no candidate listing page could be opened and inspected.
- No product draft was changed: there is no MATCH_LEVEL=A evidence.
- No production source, media, database, deployment, or GSC setting was changed.
- The post-CAPTCHA [model-first fallback](model-first-fallback-research-2026-09-02.md) found no exact manufacturer/model match and no publishable specification.

## Required hand-off

The next operator must complete Alibaba's on-page CAPTCHA in the same browser session, then resume with the seven remaining required image searches, open at least three candidate listings per product, inspect the actual gallery images, and cross-check core specifications from two independent listings before promoting any fact to a content draft.
