# Homepage Admin Mapping

Homepage chỉ được chỉnh tại `/admin/home-content`. Settings loại bỏ tab Trang chủ và backend không nhận ghi `home.*` từ form Settings.

| Admin field | Database field | Backend mapping | Frontend component | Fallback |
| --- | --- | --- | --- | --- |
| Section enabled | `home_sections.is_enabled` | boolean | `HomeSectionRenderer` bỏ section disabled | không render |
| Section order | `home_sections.sort_order` | `orderBy(sort_order)->orderBy(id)` | ordered `v-for` | không tự sắp lại |
| Variant | `home_sections.variant` | registry allow-list | section component | registry default |
| Title/subtitle/description | section columns | `heading.*` | section heading | null/ẩn |
| Hero image | `settings_json.image` | `config.image_url` | `HeroSection` | không render ảnh |
| Hero CTAs | `settings_json.primary_cta/secondary_cta` | validated config | `HeroSection` | không render CTA trống |
| Product source/limit/IDs | `settings_json` | manual/latest resolver | `ProductCollectionSection` | empty collection |
| Post source/limit/IDs | `settings_json` | manual/latest resolver | `PostCollectionSection` | empty collection |
| Item title | `home_section_items.title` | item presenter | type-specific section | null/ẩn |
| Item media | `home_section_items.image` | `MediaUrlService` | `<img v-if>` | text only |
| Item tone/avatar | `metadata_json` | registry field allow-list | relevant component only | component default style |
| Branding | `settings site.*` | one batched settings read | Header/Footer | safe text fallback |
| Logo/favicon | `site.logo`, `site.favicon` | absolute `*_url` | layout/root Blade | name/no icon |

Admin dùng registry để chỉ hiện field phù hợp với section type. Sections, items và selected products đều hỗ trợ drag reorder. Product picker hiển thị ảnh, SKU, trạng thái; Media Library vẫn là nguồn chọn ảnh.
