# Alibaba Image Search Run Log — 2026-09-02

## Outcome

STATUS=ALIBABA_CAPTCHA_BLOCKED

The user authorized uploading the local Phú Đức research images to Alibaba Image Search. Alibaba's slider CAPTCHA appeared during the fourth upload. It was not solved or bypassed.

## Upload-path evidence

| Method | Outcome | Evidence |
| --- | --- | --- |
| A. Direct hidden input plus file-chooser event | FAIL | The file-chooser event timed out when clicking input[type=file] directly. |
| B. Drag and drop | NOT_ATTEMPTED | The working browser file-chooser route was found before a file drag source was needed. |
| C. Clipboard paste | NOT_ATTEMPTED | The working browser file-chooser route was found before clipboard transmission was needed. |
| D. Alibaba Lens/camera UI | NOT_ATTEMPTED | No extension/camera permission was requested or used. |
| E. Browser file-chooser handler on visible Upload control | PASS | Accepted WebP files and returned Alibaba Image Search pages for the first three completed searches. |

## Image submissions

| Product | Image | Upload-control outcome | Result page | Observation |
| --- | --- | --- | --- | --- |
| Cẩu điện thủy lực 2 tấn | 01-primary.webp | Accepted | Yes | Mobile hydraulic-crane cards; at least 20 result cards available. |
| Cẩu điện thủy lực 2 tấn | 02-angle.webp | Accepted | Yes | Broad component/automotive results; rejected as product-family evidence. |
| Cần cẩu chân nhện | 01-primary.webp | Accepted | Yes | Spider-crane cards from several suppliers; 12 cards recorded. |
| Cần cẩu chân nhện | 02-angle.webp | Accepted | No | Alibaba displayed CAPTCHA while the image panel showed Uploading. |
| Remaining products | — | Not attempted | No | Browser work stopped at CAPTCHA. |

## Search-card evidence only

No listing was opened, so the following are shortlists rather than confirmed candidates.

### Cẩu điện thủy lực 2 tấn

- Xuzhou Bob-Lift Construction Machinery Co., Ltd. — [Multi Purpose 1 Ton 2 Ton Portable Electric Small Crane Mobile Jib Lifter for Construction and Warehouse Handling](https://www.alibaba.com/product-detail/Multi-Purpose-1-Ton-2-Ton_1601910994542.html?priceId=7e4ee26fcfd446159852beca87c2fd28)
- Xuzhou Bob-Lift Construction Machinery Co., Ltd. — [1 Ton Mini Hydraulic Crane 360° Rotating Battery Electric Pickup Truck Folding Boom](https://www.alibaba.com/product-detail/1-Ton-Mini-Hydraulic-Crane-360_1601887597992.html?priceId=7e4ee26fcfd446159852beca87c2fd28)
- Xuzhou Bob-Lift Construction Machinery Co., Ltd. — [BOB-LIFT 1T Electric Hydraulic Knuckle Boom Crane Mini 360° Rotation Pickup Truck Mounted](https://www.alibaba.com/product-detail/BOB-LIFT-1T-Electric-Hydraulic-Knuckle_1601909175927.html?priceId=7e4ee26fcfd446159852beca87c2fd28)

### Cần cẩu chân nhện

- Henan Modern Heavy Industry & Technology Co., Ltd. — [3 Ton Mini Spider Crane - Lightweight Crawler Crane for Narrow Space Operation](https://www.alibaba.com/product-detail/3-Ton-Mini-Spider-Crane-Lightweight_1601628434368.html?priceId=e6dc0ee6eb554f62800a5ccc922de127)
- Shandong Bless Machinery Co., Ltd. — [3Ton and 5Ton Dual Fuel Roof Crawler Cranes Hydraulic Telescopic Booms Suitable for Glass Installation](https://www.alibaba.com/product-detail/3Ton-and-5Ton-Dual-Fuel-Roof_1601580640764.html?priceId=e6dc0ee6eb554f62800a5ccc922de127)
- Shandong Rich Yuanda Machinery Manufacturing Co., Ltd. — [3 Ton 5 Ton 8 Ton Spider Crane Telescopic Boom Small Spyder Crane Mini Roof Crawler Crane](https://www.alibaba.com/product-detail/3-Ton-5-Ton-8-Ton_1601559092195.html?priceId=e6dc0ee6eb554f62800a5ccc922de127)

## Data-use decision

- NO_FAKE_MATCHES_CREATED=YES
- MATCH_A=0
- MATCH_B=0
- MATCH_C=5
- VERIFIED_SPEC_FIELDS=0
- SPEC_CONFLICTS=1
- CONTENT_DRAFTS_UPDATED=0
- PRODUCTION_CHANGED=NO
- GSC_CHANGED=NO
