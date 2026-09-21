# Browser/public-catalogue fallback research — Wave 1

## Scope and evidence rule

This is a second-pass public-web research log after Alibaba Image Search stopped at its on-page CAPTCHA. It records supplier/catalogue candidates found from the visible product form and marks in Phú Đức images. A candidate becomes publishable only after it is tied to the exact photographed chassis/model and independently corroborated.

`STATUS=PARTIAL_IDENTIFICATION_NO_PUBLISHABLE_CONFIG`

| Product | Best lead found | Candidate evidence | Result | Publishable fields |
| --- | --- | --- | --- | --- |
| Cẩu điện thủy lực 2 tấn | 1/2/3-ton truck-mounted hydraulic-crane family | [Three-section, 360-degree truck crane](https://chenglancrane.en.made-in-china.com/product/RaOpUHTMvcrP/China-1-Ton-Full-Electric-Pure-Copper-Motor-360-Degree-Rotation-Truck-Mounted-Crane.html) | Same equipment family only. The Phú Đức primary image itself says `1 tấn`, so the site title's 2-ton claim is not resolved. | None |
| Cần cẩu chân nhện | 3-ton crawler spider-crane family | [HC3.0 catalogue](https://www.iwrcw.com/products/3-ton-spider-crane.html) and [YG-3 catalogue](https://www.ygmachine.com/3-ton-spider-crane-price/) | Similar yellow crawler/four-outrigger form, but no make/model is visible on the Phú Đức machine and the public page mixes 1/5/8/12-ton variants. | None |
| Cẩu trục chạy điện có chân di chuyển | 2-ton mobile gantry, 3-m span/lift example | [2-ton mobile gantry case](https://www.djcranes.com/2-Ton-Mobile-Gantry-Crane-for-Chile-customer.html) | The source supports a market configuration, not the yellow/red configurations in the Phú Đức gallery. | None |
| Máy xúc lật 4 bánh gầu 0,4 m³ | `YGZG` logo only | [Yuan Gong/YGZG listing](https://germany.alibaba.com/product-detail/YGZG-Quiet-Loader-Low-Decibel-Operation_1601708304656.html) | The listing names `YGONG-817`; that model is not readable on the Phú Đức image, and the page does not map gallery images to electric/diesel variants. | None |
| Xe nâng điện | CPD-15 and CPD35 capacity-class marks; probable source image for CPD35 | [CPD35 supplier page](https://www.ecer.com/corp/details-uuueamc-p1b014y-china-60v-3-5-tons-5-tons-small-electric-forklift-price-battery-forklift-electric-forklift-for-sale.html) plus two manufacturer catalogues below | The CPD35 image search resolves to a commercial image family; manufacturer identity remains absent and listings give incompatible battery/configuration values. | None |

## Candidate-only technical readings

These values describe the cited candidates, **not** the Phú Đức products. They are retained only to make a later plate/datasheet check fast.

| Candidate | Values reported by source | Why not adopted |
| --- | --- | --- |
| Truck-mounted hydraulic crane | 1/2/3-ton options; 3-section hydraulic boom; 360-degree rotation; 2.8-m lifting height; 12/24 V. | It is a configurable family listing and the observed 1-ton image label conflicts with the page name. |
| HC3.0 spider crane | 3 t; 5-section boom; 2.5–9.5 m boom range; 10-m ground lift; crawler; four outriggers; diesel/electric alternatives. | No exact manufacturer/model match. Other 3-ton catalogues give different dimensions, engines and operating data. |
| 2-ton mobile gantry | 2 t; 3-m span; 3-m lifting height; 380 V / 50 Hz / 3 phase; electric chain hoist. | The Phú Đức page includes several load variants and several visually different gantries. |
| CPD35 forklift lead | The image-family supplier page calls it `CPD-35`, with 60 V / 630 Ah and 3–6 m lift options. Independent manufacturer catalogues use CPD35 for 3,500 kg, but one lists 80 V / 500 Ah and other configuration differences. | A reseller image and generic model code do not identify the photographed manufacturer or its exact configuration. |

## CPD35 corroboration and conflict

| Source | What it supports | What it cannot support |
| --- | --- | --- |
| [Ming Sheng CPD-35 commercial listing](https://www.ecer.com/corp/details-uuueamc-p1b014y-china-60v-3-5-tons-5-tons-small-electric-forklift-price-battery-forklift-electric-forklift-for-sale.html) | A CPD-35 commercial image/configuration with 60 V / 630 Ah and 3–6 m lift options. | Exact manufacture of the Phú Đức photo. |
| [SINOMACH CPD35](https://sinomachhiglobal.com/8-2-8-cpd35-ac3-electric-forklift.html) | CPD35 is used for a 3,500-kg capacity class. | Its 80 V / 500 Ah configuration cannot be transplanted to the photographed truck. |
| [LiuGong CPD35](https://liugongindia.com/products/cpd35/) | A second manufacturer also uses CPD35 for 3,500 kg. | It is a separate machine/configuration. |

Therefore `CPD35 = 3.5-ton capacity-class lead` is reasonable for the **02-angle image only**, but it remains `NON_PUBLISHABLE` until a nameplate or supplier datasheet identifies its actual make and variant. `CPD-15` remains separately associated with the **01-primary image**; the page must not be treated as one homogeneous configuration.

## Required final evidence

For each product, obtain either (a) a sharp, readable photo of the serial/model plate, or (b) a supplier datasheet that explicitly names the pictured model and its configuration. For the hydraulic crane, also resolve the visible 1-ton label versus the 2-ton page title before publishing any load chart or capacity claim.

## Change boundary

- `CONTENT_DRAFTS_UPDATED=0`
- `PRODUCTION_CHANGED=NO`
- `GSC_CHANGED=NO`
