# Google Lens reverse-image research — Wave 1

## Method and status

On 2026-09-07, each public Phú Đức product-image URL below was submitted to Google Lens in the user's Chrome Work profile through **Paste image link**. This is reverse-image search of the actual image, not a text-only search. Local-file upload was unavailable in the Chrome extension, so no local-only asset was sent; the already-public product image URLs were used instead.

`STATUS=IDENTIFIERS_AND_VISUAL_SOURCES_FOUND_PARTIAL_CONFIG_VERIFICATION`

## Outcome matrix

| Phú Đức product / image | Google Lens result | Strongest source found | Evidence decision |
| --- | --- | --- | --- |
| Cẩu điện thủy lực 2 tấn / `01-primary.webp` | OCR reads `1 tấn`; Lens identifies a compact 1-ton electric-hydraulic crane with 360-degree slewing base. | [Cửa Hàng Vật Tư — Cầu cẩu Telescopic xoay 360 độ](https://cuahangvattu.com/products/cau-cau-xoay-360-do-tai) | `MATCH_B_HIGH`: visual source and product form agree, but no manufacturer plate is visible. The 1-ton image evidence conflicts with the Phú Đức 2-ton page title. |
| Cần cẩu chân nhện / `01-primary.webp` | Lens identifies crawler spider crane, telescopic boom and four hydraulic outriggers; its top visual match is a 5-ton listing. | [Jiusheng JS5.0 spider crane](https://truckcranecn.en.made-in-china.com/product/zfPrRdqAIapH/China-5t-Telescopic-Boom-Spider-Crane-Warehouse-Handling-Multi-Function-Mini-Crawler-Spider-Crane.html) | `MATCH_B`: 5-ton is a plausible gallery variant, but the Phú Đức page lacks image-to-variant mapping and the supplier page gives conflicting 5-ton data. |
| Cẩu trục chạy điện có chân di chuyển / `01-primary.webp` | Lens identifies a yellow portable gantry with electric chain hoist and caster wheels. | [Kino portable gantry](https://www.kinocranes.com/vi/product/portable-gantry-crane/) | `MATCH_C`: product family only; no readable model or capacity/span/height mapping. |
| Máy xúc lật 4 bánh gầu 0,4 m³ / `02-angle.webp` | Lens OCR returns `907` and a partly legible brand-like mark; its leading visual result is a 904 diesel wheel-loader listing. | [HRD-904 diesel loader](https://www.alibaba.com/product-detail/Small-Diesel-904-Four-Wheel-Drive_1601561938037.html) | `MATCH_B_LOW`: form is close, but the OCR lead `907` conflicts with source model `HRD-904`; do not equate rated load with bucket volume. |
| Xe nâng điện / `01-primary.webp` | Lens reads `CPD-15` and identifies 1.5-ton electric forklift. | [Alibaba visual match](https://www.alibaba.com/product-detail/Electric-Forklift-1-5-Ton-2_1601730301058.html) | `MATCH_B`: the image supports the CPD-15 / 1.5-ton capacity class only. |
| Xe nâng điện / `02-angle.webp` | Lens reads `CPD35` and identifies 3.5-ton electric counterbalance forklift. | [Huateng HT-3.5T](https://www.huatengjt.com/product/electric_forklift/39.html) | `MATCH_B_HIGH`: direct Lens visual match gives an inspectable source configuration, but Huateng's product designation differs from the CPD35 photo mark. |

## Candidate specification records

### Hydraulic crane — 1-ton photo, not 2-ton proof

The source's selectable variants are 1 ton manual rotation/manual lever control and 1 ton automatic rotation/remote control. It states: 12 VDC / 24 VDC / 220 VAC supply; manual or remote electric hydraulic-cylinder control; manual or hydraulic-motor rotation; maximum 1,000 kg with capacity reduced at longer boom reach; 0–140-degree boom angle; 150 kg machine weight; 75 cm telescopic stroke; two boom sections for manual variant and three for automatic/remote variant.

`PUBLISH_DECISION=BLOCKED_BY_1T_VS_2T_CONFLICT`

### Spider crane — 5-ton candidate

The Jiusheng page labels the selected product `JS5.0`, 5 t, hydraulic outriggers and 360-degree rotation. Its 5-ton comparison row gives: 16.8 m lift height, 16 m × 0.21 t working-radius point, 11 kW, 6.55 t vehicle weight, 11.7 m / 45 s telescoping and 4,600 × 1,430 × 2,170 mm overall size. But the page's basic-info panel simultaneously says 22 m maximum lift height and 15 kW.

`PUBLISH_DECISION=BLOCKED_BY_SOURCE_CONFLICT_AND_NO_GALLERY_VARIANT_MAPPING`

### Electric forklifts — separate photo configurations

| Photo mark | Lens capacity result | Visual-source configuration | Decision |
| --- | ---: | --- | --- |
| CPD-15 | 1,500 kg | Lens result calls out a battery-powered four-wheel forklift and 3-m lifting in the linked visual result title. | Capacity-class only; no mast, battery or dimension field is adopted. |
| CPD35 | 3,500 kg | Huateng HT-3.5T: 2,750 × 1,220 × 2,180 mm without forks; wheelbase 1,860 mm; 3,000 mm lift (customizable); 12 km/h no-load and 10 km/h laden speed; front 28×9-15 and rear 650-10 tyres; hydraulic power steering. | Source configuration only; the photo's manufacturer remains unconfirmed. |

`PUBLISH_DECISION=CAPACITY_CLASS_CONFIRMED_PER_PHOTO; OTHER_FIELDS_NON_PUBLISHABLE`

### Compact loader — HRD-904 visual candidate

The Alibaba listing gives `HRD-904`, 400 kg rated load, 18/22 hp, 850 kg machine weight, 2.8 × 1.0 × 1.5 m dimensions, 1.6 m dumping height and 2.3 m lifting height (customizable). Its bucket-capacity field is unspecified. This must not be converted to the Phú Đức claim of a 0.4 m³ bucket.

`PUBLISH_DECISION=BLOCKED_BY_907_VS_904_IDENTIFIER_CONFLICT_AND_MISSING_BUCKET_VOLUME`

### Portable gantry

Lens verifies only the visual family: wheeled A-frame portable gantry with electric chain hoist. It returns sources spanning 0.5–10 t, so none can establish the page's 2/3/5-ton variants or its dimensional/electrical data.

`PUBLISH_DECISION=NO_EXACT_MODEL_OR_VARIANT`

## Final data boundary

- The hydraulic-crane photo is strong evidence that its photographed configuration is **1 t**, and it must not be used to substantiate a 2-t claim.
- CPD-15 and CPD35 are separate photographed forklift configurations: **1.5 t** and **3.5 t** capacity classes, respectively.
- No vendor price, warranty, origin, rating, delivery or certification claim is to be copied to Phú Đức content.
- No production page, draft, database, media asset, deployment or GSC setting was modified.
