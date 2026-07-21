# Homepage Content Contract

## Top-level payload

`GET /` trả đúng ba nhánh dữ liệu storefront:

```json
{
  "site": {
    "name": "Phú Đức",
    "tagline": null,
    "description": null,
    "logo_url": null,
    "favicon_url": null,
    "phone": null,
    "hotline": null,
    "email": null,
    "address": null,
    "working_hours": null,
    "copyright": null,
    "social_links": { "facebook": null, "zalo": null, "youtube": null }
  },
  "navigation": { "header": [], "footer": [] },
  "page": { "type": "home", "seo": {}, "json_ld": {}, "sections": [] }
}
```

`page.sections` là ordered array. Storefront không `keyBy` để quyết định vị trí và không nhận các prop rời `settings`, `featuredProducts`, `latestPosts` hay `homeSections`.

## Section envelope

Mọi section có cùng envelope:

```json
{
  "key": "featured_products",
  "type": "product_collection",
  "enabled": true,
  "sort_order": 40,
  "variant": "marketplace_grid",
  "heading": {
    "eyebrow": null,
    "title": "Sản phẩm nổi bật",
    "subtitle": null,
    "description": null
  },
  "config": { "source": "manual", "limit": 4, "product_ids": [10, 5] },
  "items": []
}
```

Registry chuẩn nằm tại `app/Support/Homepage/HomeSectionRegistry.php` và định nghĩa 10 key: `hero`, `category_cards`, `benefit_strip`, `featured_products`, `energy_banner`, `industry_solutions`, `testimonials`, `partners`, `latest_posts`, `consultation_steps`.

## Data sources

| Section | Type | Data source |
| --- | --- | --- |
| hero | `hero` | `home_sections` config |
| category_cards | `item_collection` | active section items |
| benefit_strip | `item_collection` | active section items |
| featured_products | `product_collection` | `manual` hoặc active `latest` products |
| energy_banner | `content_banner` | section heading/config |
| industry_solutions | `item_collection` | active section items |
| testimonials | `item_collection` | active section items |
| partners | `item_collection` | active section items |
| latest_posts | `post_collection` | `manual` hoặc published `latest` posts |
| consultation_steps | `item_collection` | active section items |

Manual product/post mode giữ đúng thứ tự ID, bỏ record bị xóa/inactive và không tự bù record khác. Limit hợp lệ từ 1 đến 12.

## Empty-state rule

Section có nguồn dữ liệu nhưng `items` rỗng không được tạo card mẫu, dùng fallback giả, tự bù item hoặc tự chuyển data source. Storefront ẩn toàn bộ các collection section không còn nội dung hữu ích. `hero` vẫn có thể render khi có title nhưng không có ảnh; `energy_banner` vẫn có thể render khi có nội dung text nhưng không có ảnh trang trí.

## Item contract

Item do Admin quản lý có `id`, `title`, `subtitle`, `description`, `image_url`, `icon`, `url`, `metadata`, `enabled`, `sort_order`. Chỉ item active được public. `image_url` luôn do backend resolve.

Product card có `id`, `name`, `slug`, `sku`, `price`, `price_display`, `image_url`, ordered `specifications`, tối đa ba `card_specifications`, `review_count` và `average_rating`. Không có specification hoặc review giả.
