# ALIBABA PRODUCT MATCH

## Phú Đức product

- Name: Xe nâng điện
- URL: <https://phuduc.com/san-pham/xe-nang-dien>
- Slug: `xe-nang-dien`

## Search images

| Local file | Public source URL | Note |
| --- | --- | --- |
| `../search-images/xe-nang-dien/01-primary.webp` | <https://phuduc.com/storage/media/xe-nang-dien/xe-nang-dien-anh-san-pham-01.webp> | Red counterbalance forklift, visible `CPD-15` marking. |
| `../search-images/xe-nang-dien/02-angle.webp` | <https://phuduc.com/storage/media/xe-nang-dien/xe-nang-dien-anh-san-pham-02.webp> | Red forklift, visible `CPD35` marking. |
| `../search-images/xe-nang-dien/03-detail.webp` | <https://phuduc.com/storage/media/xe-nang-dien/xe-nang-dien-anh-san-pham-04.webp> | Rear/side forklift view. |

## Candidates

| Candidate | URL | Supplier | Title / model | Visual confidence | Spec confidence | Result |
| --- | --- | --- | --- | --- | --- | --- |
| A | Not collected | — | — | — | — | Not submitted: Alibaba CAPTCHA stopped the run before this product. |
| B | Not collected | — | — | — | — | Multiple apparent models are visible; do not treat them as one variant. |
| C | Not collected | — | — | — | — | Manual image-search hand-off required. |

## Verified specs

| Field | Value | Confidence | Source 1 | Source 2 | Notes |
| --- | --- | --- | --- | --- | --- |
| None from Alibaba | — | — | — | — | No Alibaba result/listing was collected. |

## Conflicts and missing data

- The public page lists 1, 1.5, 2.5 and 3.5-ton variants, while selected gallery photos show separate apparent model marks (`CPD-15` and `CPD35`) without a catalog mapping.
- Missing: model-to-capacity mapping, mast/lift height, load centre, battery/charger, dimensions, weight, tyres, speed, and warranty.

## Safe / unsafe facts

- Safe: selected images depict red counterbalance forklifts and page-level variant labels are public data.
- Unsafe: using a model mark, photo, or visual Alibaba look-alike as proof of any capacity, mast, battery, charger, or model specification.

## 2026-09-02 actual run status

- The visible Alibaba Upload control and browser file-chooser handler worked for earlier Wave 1 images.
- This product's local images were not submitted because Alibaba displayed a slider CAPTCHA during the fourth Wave 1 upload.
- No candidate, image source, visual score, or Alibaba specification was collected for this product.

## Model-first fallback result

- Visible marks: CPD-15 and CPD35.
- Two manufacturer catalogues per code agree on 1.5-ton and 3.5-ton capacity classes, respectively; see [fallback research](../model-first-fallback-research-2026-09-02.md).
- The photographed manufacturer and exact variant are not identified, so those values remain non-publishable and every configuration field remains DATA_MISSING.

`MATCH_LEVEL=C` — not evaluated against Alibaba results; this is not evidence of a matching supplier.

`RECOMMENDED_ACTION=MANUAL_REVIEW`

`MANUAL_REVIEW_REQUIRED=YES`

## 2026-09-07 browser/public-catalogue supplement

- A public supplier result resolves an image family labelled `CPD-35`, giving a stronger lead for `02-angle.webp`; it is documented in the [browser/public-catalogue research log](../browser-public-fallback-research-2026-09-07.md).
- CPD35 is corroborated as a 3.5-ton **capacity-class** code by two separate manufacturer catalogues, but their battery and machine configurations differ.
- This raises the `02-angle.webp` lead above a generic visual guess, but does **not** identify the photographed manufacturer. No voltage, battery, mast, lift height, dimensions, weight, speed, warranty or certification is publishable for Phú Đức.

## 2026-09-07 Google Lens reverse-image result

Lens searched the actual public image URLs and independently read `CPD-15` on `01-primary.webp` and `CPD35` on `02-angle.webp`. It assigns the photos to separate 1.5-ton and 3.5-ton capacity classes. The CPD35 visual source has a detailed HT-3.5T configuration, but it is still a source configuration rather than manufacturer identification for the photographed Phú Đức unit. See the [full Lens research record](../google-lens-reverse-image-research-2026-09-07.md).
