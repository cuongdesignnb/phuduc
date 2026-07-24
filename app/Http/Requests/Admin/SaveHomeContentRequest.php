<?php

namespace App\Http\Requests\Admin;

use App\Rules\MediaAssetRule;
use App\Support\Homepage\HomeSectionRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveHomeContentRequest extends FormRequest
{
    private const COMMON_ITEM_FIELDS = ['id', 'enabled', 'sort_order', 'metadata'];

    private const BUSINESS_ITEM_FIELDS = ['title', 'subtitle', 'description', 'image', 'icon', 'url'];

    private const METADATA_ITEM_FIELDS = ['tone', 'avatar_text'];

    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        $definitions = HomeSectionRegistry::definitions();
        $rules = [
            'sections' => ['required', 'array', 'max:'.count($definitions)],
            'version' => ['nullable', 'string', 'max:100'],
            'sections.*.key' => ['required', 'string', 'distinct', Rule::in(array_keys($definitions))],
            'sections.*.type' => ['required', 'string'],
            'sections.*.enabled' => ['required', 'boolean'],
            'sections.*.sort_order' => ['required', 'integer', 'min:0'],
            'sections.*.variant' => ['required', 'string', 'max:100'],
            'sections.*.heading' => ['required', 'array:eyebrow,title,subtitle,description'],
            'sections.*.heading.eyebrow' => ['nullable', 'string', 'max:150'],
            'sections.*.heading.title' => ['nullable', 'string', 'max:300'],
            'sections.*.heading.subtitle' => ['nullable', 'string', 'max:300'],
            'sections.*.heading.description' => ['nullable', 'string', 'max:3000'],
            'sections.*.config' => ['present', 'array'],
            'sections.*.config.image_media_id' => ['nullable', 'integer', MediaAssetRule::image()],
            'sections.*.config.product_ids' => ['sometimes', 'array', 'max:12'],
            'sections.*.config.post_ids' => ['sometimes', 'array', 'max:12'],
            'sections.*.items' => ['present', 'array'],
            'sections.*.items.*.id' => ['nullable', 'integer'],
            'sections.*.items.*.title' => ['nullable', 'string', 'max:255'],
            'sections.*.items.*.subtitle' => ['nullable', 'string', 'max:255'],
            'sections.*.items.*.description' => ['nullable', 'string', 'max:3000'],
            'sections.*.items.*.image' => ['nullable', 'string', 'max:500'],
            'sections.*.items.*.media_id' => ['nullable', 'integer', MediaAssetRule::image()],
            'sections.*.items.*.icon' => ['nullable', 'string', 'max:100'],
            'sections.*.items.*.url' => ['nullable', 'string', 'max:500'],
            'sections.*.items.*.metadata' => ['present', 'array'],
            'sections.*.items.*.metadata.tone' => ['nullable', 'string', 'max:100'],
            'sections.*.items.*.metadata.avatar_text' => ['nullable', 'string', 'max:3'],
            'sections.*.items.*.enabled' => ['required', 'boolean'],
            'sections.*.items.*.sort_order' => ['required', 'integer', 'min:0'],
        ];

        foreach ($this->input('sections', []) as $index => $section) {
            $key = $section['key'] ?? null;
            $prefix = "sections.$index";

            if ($key === 'hero') {
                $rules["$prefix.heading.title"] = ['required', 'string', 'max:300'];
                $rules["$prefix.config.image"] = ['nullable', 'string', 'max:500'];
                $rules["$prefix.config.primary_cta"] = ['nullable', 'array:label,url'];
                $rules["$prefix.config.primary_cta.label"] = ['nullable', 'string', 'max:100'];
                $rules["$prefix.config.primary_cta.url"] = ['nullable', 'string', 'max:500'];
                $rules["$prefix.config.secondary_cta"] = ['nullable', 'array:label,action,url'];
                $rules["$prefix.config.secondary_cta.label"] = ['nullable', 'string', 'max:100'];
                $rules["$prefix.config.secondary_cta.action"] = ['nullable', Rule::in(['phone', 'url'])];
                $rules["$prefix.config.secondary_cta.url"] = ['nullable', 'string', 'max:500'];
            }

            if ($key === 'featured_products') {
                $rules["$prefix.config.source"] = ['required', Rule::in(['manual', 'latest'])];
                $rules["$prefix.config.limit"] = ['required', 'integer', 'min:1', 'max:12'];
                $rules["$prefix.config.product_ids"] = ['present', 'array'];
                $rules["$prefix.config.product_ids.*"] = ['integer', 'distinct', 'exists:products,id'];
            }

            if ($key === 'latest_posts') {
                $rules["$prefix.config.source"] = ['required', Rule::in(['manual', 'latest'])];
                $rules["$prefix.config.limit"] = ['required', 'integer', 'min:1', 'max:12'];
                $rules["$prefix.config.post_ids"] = ['present', 'array'];
                $rules["$prefix.config.post_ids.*"] = ['integer', 'distinct', 'exists:posts,id'];
            }

            if ($key === 'energy_banner') {
                $rules["$prefix.config.eyebrow"] = ['nullable', 'string', 'max:150'];
                $rules["$prefix.config.image"] = ['nullable', 'string', 'max:500'];
                $rules["$prefix.config.stats"] = ['nullable', 'array', 'max:2'];
                $rules["$prefix.config.stats.*"] = ['array:label,value'];
                $rules["$prefix.config.stats.*.label"] = ['nullable', 'string', 'max:150'];
                $rules["$prefix.config.stats.*.value"] = ['nullable', 'string', 'max:100'];
            }
        }

        return $rules;
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            foreach ($this->input('sections', []) as $sectionIndex => $section) {
                $definition = HomeSectionRegistry::get($section['key'] ?? '');
                if (! $definition) {
                    continue;
                }

                if (($section['type'] ?? null) !== $definition['type']) {
                    $validator->errors()->add("sections.$sectionIndex.type", 'Section type không khớp registry.');
                }
                if (! in_array($section['variant'] ?? null, $definition['allowed_variants'], true)) {
                    $validator->errors()->add("sections.$sectionIndex.variant", 'Variant không được hỗ trợ.');
                }

                $unknownConfig = array_diff(array_keys($section['config'] ?? []), array_merge($definition['config_keys'], ['image_media_id']));
                if ($unknownConfig !== []) {
                    $validator->errors()->add("sections.$sectionIndex.config", 'Config chứa field không được registry cho phép.');
                }
                if (! $definition['supports_items'] && ! empty($section['items'])) {
                    $validator->errors()->add("sections.$sectionIndex.items", 'Section này không hỗ trợ item thủ công.');
                }

                $allowedBusinessFields = array_intersect($definition['item_fields'], self::BUSINESS_ITEM_FIELDS);
                $allowedItemKeys = array_merge(self::COMMON_ITEM_FIELDS, ['media_id'], $allowedBusinessFields);
                $allowedMetadata = array_intersect($definition['item_fields'], self::METADATA_ITEM_FIELDS);
                foreach ($section['items'] ?? [] as $itemIndex => $item) {
                    if (array_diff(array_keys($item), $allowedItemKeys) !== []) {
                        $validator->errors()->add(
                            "sections.$sectionIndex.items.$itemIndex",
                            'Item contains a field that is not allowed for this section.'
                        );
                    }

                    if (array_diff(array_keys($item['metadata'] ?? []), $allowedMetadata) !== []) {
                        $validator->errors()->add("sections.$sectionIndex.items.$itemIndex.metadata", 'Metadata chứa field không được section này cho phép.');
                    }
                }
            }
        }];
    }
}
