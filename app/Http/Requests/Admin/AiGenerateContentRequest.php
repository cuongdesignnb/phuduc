<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AiGenerateContentRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->is_admin; }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['article', 'seo', 'product_description', 'category_description'])],
            'topic' => ['nullable', 'string', 'max:500'],
            'keywords' => ['nullable', 'array', 'max:20'],
            'keywords.*' => ['string', 'max:100'],
            'tone' => ['required', Rule::in(['professional', 'casual', 'luxury'])],
            'length' => ['required', Rule::in(['short', 'medium', 'long'])],
            'full_article' => ['sometimes', 'boolean'],
            'existing_content' => ['nullable', 'string', 'max:100000'],
            'category_id' => ['nullable', 'integer', 'exists:post_categories,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'with_images' => ['sometimes', 'boolean'],
            'image_count' => ['nullable', 'integer', 'min:1', 'max:4'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (blank($this->input('topic')) && blank($this->input('product_id'))) {
                $validator->errors()->add('topic', 'Chủ đề là bắt buộc nếu không chọn sản phẩm.');
            }
        });
    }
}
