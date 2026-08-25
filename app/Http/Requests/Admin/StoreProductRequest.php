<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->is_admin; }

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->input('name')), 'slug' => trim((string) $this->input('slug', '')), 'sku' => trim((string) $this->input('sku', ''))]);
    }

    public function rules(): array
    {
        return self::baseRules();
    }

    public static function baseRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', 'unique:products,slug'],
            'sku' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:50000'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'meta_keywords' => ['nullable', 'string', 'max:1000'],
            'price' => ['nullable', 'integer', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'specifications' => ['nullable', 'array', 'max:50'],
            'specifications.*.key' => ['nullable', 'string', 'max:150'],
            'specifications.*.value' => ['nullable', 'string', 'max:500'],
            'variants' => ['nullable', 'array', 'max:50'],
            'variants.*.name' => ['required', 'string', 'max:255'],
            'variants.*.image_id' => ['nullable', 'integer', 'exists:product_images,id'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.price' => ['required', 'integer', 'min:0'],
            'variants.*.stock' => ['required', 'integer', 'min:0'],
            'variants.*.note' => ['nullable', 'string', 'max:1000'],
            'variants.*.status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $keys = collect($this->input('specifications', []))->map(fn ($item) => mb_strtolower(trim((string) ($item['key'] ?? ''))))->filter();
            if ($keys->duplicates()->isNotEmpty()) {
                $validator->errors()->add('specifications', 'Tên thông số không được trùng nhau.');
            }
            foreach ($this->input('specifications', []) as $index => $item) {
                if (blank($item['key'] ?? null) && filled($item['value'] ?? null)) {
                    $validator->errors()->add("specifications.$index.key", 'Tên thông số là bắt buộc khi có giá trị.');
                }
            }

            $variantNames = collect($this->input('variants', []))
                ->map(fn ($item) => mb_strtolower(trim((string) ($item['name'] ?? ''))))
                ->filter();
            if ($variantNames->duplicates()->isNotEmpty()) {
                $validator->errors()->add('variants', 'Tên biến thể không được trùng nhau.');
            }
        });
    }
}
