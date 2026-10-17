# Alibaba Image Search — Wave 1 worklist

## Current run status

- STATUS=ALIBABA_CAPTCHA_BLOCKED
- Date: 2026-09-02
- Upload method that succeeded: visible Alibaba Upload control plus browser file-chooser handler (no native Windows file picker).
- Direct hidden file-input activation first timed out. This did not end the run: the visible control completed the first three result-producing searches and accepted a fourth upload.
- Results returned: Cẩu điện thủy lực 2 tấn image 01-primary, Cẩu điện thủy lực 2 tấn image 02-angle, and Cần cẩu chân nhện image 01-primary.
- Cần cẩu chân nhện image 02-angle was accepted by the upload control, then Alibaba presented a slider CAPTCHA while the panel showed Uploading. No result page was collected for that submission.
- Do not solve or bypass the CAPTCHA programmatically. Resume only after a human clears the site verification.

## Remaining browser work after human verification

1. Re-check the interrupted Cần cẩu chân nhện 02-angle upload and record whether a result page is returned.
2. Search at least one more full-machine image for Cần cẩu chân nhện where possible.
3. Search at least two images each for Cẩu trục chạy điện có chân di chuyển, Máy xúc lật 4 bánh gầu 0,4 m³, and Xe nâng điện.
4. For every returned result set, inspect 10–20 cards where available and open the strongest three listing pages for the product.
5. Compare gallery images mechanically: chassis/frame, wheels or crawler, outriggers, boom/arm, power unit, control layout, attachment, decals, and other distinctive details.
6. Record exact listing URLs, supplier, model, gallery-image URLs, and only specifications supported by two independent candidate listings.
7. Keep a conflicting field as DATA_MISSING; do not average it or select a preferred value.

## Actual result-card shortlists

- Cẩu điện thủy lực 2 tấn: the primary image returned a large mobile hydraulic-crane result set. Search-card titles included 1-ton, 1–2-ton, and 2-ton claims; this is not a capacity verification. The 02-angle detail image returned primarily compressors and hydraulic components, therefore it is rejected as a product-family signal.
- Cần cẩu chân nhện: the primary image returned spider-crane cards from multiple suppliers, including 3-ton, 3/5-ton, and 3/5/8-ton family claims. Search ranking alone is not a visual score or specification verification.

## Image inventory and cautions

| Product | Local files | Public source pages | Visual cue / caution |
| --- | --- | --- | --- |
| Cẩu điện thủy lực 2 tấn | search-images/cau-dien-thuy-luc-2-tan/ | [product page](https://phuduc.com/san-pham/cau-dien-thuy-luc-2-tan) | Orange hydraulic crane; 01-primary visibly bears 1 tấn, so it must not prove a 2-ton claim. 02 and 03 are component close-ups, not second full-product angles. |
| Cần cẩu chân nhện | search-images/can-cau-chan-nhen/ | [product page](https://phuduc.com/san-pham/can-cau-chan-nhen) | Yellow crawler mini-crane with four outriggers. Page variants are 1/5/8/12 tấn, but gallery-to-variant mapping is absent. |
| Cẩu trục chạy điện có chân di chuyển | search-images/cau-truc-chay-dien-co-chan-di-chuyen/ | [product page](https://phuduc.com/san-pham/cau-truc-chay-dien-co-chan-di-chuyen) | Mobile gantry-crane forms on casters; gallery includes different colours/configurations. |
| Máy xúc lật 4 bánh gầu 0,4 m³ | search-images/may-xuc-lat-4-banh-gau-0-4-m3/ | [product page](https://phuduc.com/san-pham/may-xuc-lat-4-banh-gau-0-4-m3) | Compact four-wheel orange loader and bucket; page has electric and diesel variants without image mapping. |
| Xe nâng điện | search-images/xe-nang-dien/ | [product page](https://phuduc.com/san-pham/xe-nang-dien) | Red counterbalance forklifts with apparent model/capacity labels that require plate/datasheet verification. |

## Non-negotiable evidence rules

- Image similarity is not specification evidence.
- Never copy Alibaba price, MOQ, delivery, warranty, origin, certification, or supplier marketing claim into Phú Đức content.
- A safe content fact requires two independent sources with agreement and a MATCH_LEVEL=A decision.
- The research images remain local evidence only; no Alibaba image may be uploaded to Phú Đức production.
