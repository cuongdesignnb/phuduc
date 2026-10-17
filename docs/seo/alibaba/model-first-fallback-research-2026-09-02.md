# Model-first fallback research — Wave 1

## Status

STATUS=IDENTIFIERS_PARTIALLY_FOUND_NO_EXACT_MODEL_MATCH

This research path was used after Alibaba Image Search was stopped by its CAPTCHA. It uses visible image marks and public manufacturer/supplier catalogues. It does not replace the required exact-chassis and two-listing verification workflow.

## Visible marks found in Phú Đức research images

| Product | Visible mark / observation | Identification result | Content decision |
| --- | --- | --- | --- |
| Cẩu điện thủy lực 2 tấn | 1 tấn is visibly overlaid on the 01-primary image; no manufacturer/model plate is legible. | Capacity conflict remains unresolved. | Do not use any Alibaba/catalogue specification. |
| Cần cẩu chân nhện | Yellow crawler chassis, four outriggers and telescopic boom; no unique manufacturer/model mark is legible. | Generic spider-crane family only. | Do not use 3-ton family catalogues as this machine's specification. |
| Cẩu trục chạy điện có chân di chuyển | Yellow portable gantry on casters; no unique model or plate is legible. | Generic portable-gantry family only. | Keep all capacity/span/height/electrical fields as DATA_MISSING. |
| Máy xúc lật 4 bánh gầu 0,4 m³ | YGZG is visibly printed as a logo; the boom/body marking is not legible enough to prove a model. | Possible Yuan Gong/YGZG brand lead, not an exact model match. | Do not adopt capacity, bucket, engine, or dimensions. |
| Xe nâng điện | CPD-15 on one image and CPD35 on another; no manufacturer brand is legible. | Two capacity-class identifiers, but not exact manufacturer models. | Do not adopt voltage, battery, mast, dimensions, weight, or speed. |

## Catalogue cross-check: electric forklift codes

| Visible code | Manufacturer catalogue A | Manufacturer catalogue B | Agreement | Why it is still not publishable |
| --- | --- | --- | --- | --- |
| CPD-15 | [Anxing CPD-15](https://anxingmachinery.com/products/1-5-ton-electric-forklift/) lists 1,500 kg. | [SC-MaxTech CPD-15](https://www.scmaxtech.com/pd.jsp?id=192) lists 1,500 kg. | Both describe a 1.5-ton capacity class. | Neither source is identified as the manufacturer of the photographed truck; their configurations differ. |
| CPD35 | [SINOMACH CPD35](https://sinomachhiglobal.com/8-2-8-cpd35-ac3-electric-forklift.html) lists 3,500 kg. | [LiuGong CPD35](https://liugongindia.com/products/cpd35/) lists 3,500 kg. | Both describe a 3.5-ton capacity class. | The photographed truck's brand and exact variant are unverified; catalogue configurations differ. |

The sources above support only an interpretation of the visible CPD capacity-class labels. They do not verify a Phú Đức product configuration or a safe content field.

## Rejected catalogue leads

- YGZG appears in a public [Yuan Gong supplier listing](https://germany.alibaba.com/product-detail/YGZG-Quiet-Loader-Low-Decibel-Operation_1601708304656.html) for model YGONG-817. The Phú Đức loader image does not expose YGONG-817, so that listing is not an exact-model source.
- Public spider-crane catalogues returned many different 3-ton layouts, boom lengths, power systems, and dimensions. With no readable model on the Phú Đức image, none can be mapped safely.
- The portable gantry image has no legible manufacturer/model identifier. Generic gantry catalogues are not evidence for this product's 2/3/5-ton variants.

## Final evidence decision

- MODEL_IDENTIFIERS_WITH_EXACT_MAKE_MATCH=0
- CAPACITY_CLASS_HINTS=2
- VERIFIED_SPEC_FIELDS=0
- CONTENT_DRAFTS_UPDATED=0
- PRODUCTION_CHANGED=NO

The next evidence needed is a sharp image of each serial/model plate or a Phú Đức supplier datasheet tied to the photographed configuration.

## 2026-09-07 public-web supplement

The later [browser/public-catalogue research log](browser-public-fallback-research-2026-09-07.md) found source-backed equipment-family candidates and a stronger CPD35 image-family lead. It also confirmed configuration conflicts across CPD35 catalogues. No exact make-and-model match was obtained, so the original decision remains unchanged: no candidate specification is promoted into a Phú Đức content draft.
