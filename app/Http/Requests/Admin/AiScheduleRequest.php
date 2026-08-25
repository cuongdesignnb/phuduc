<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AiScheduleRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->is_admin; }

    protected function prepareForValidation(): void
    {
        $keywords = $this->input('keywords');
        if (is_string($keywords)) {
            $keywords = preg_split('/[,\n]+/', $keywords, -1, PREG_SPLIT_NO_EMPTY);
        }
        $this->merge(['keywords' => array_values(array_filter(array_map('trim', (array) $keywords)))]);
    }

    public function rules(): array
    {
        return [
            'topic' => ['required', 'string', 'max:500'],
            'keywords' => ['nullable', 'array', 'max:20'],
            'keywords.*' => ['string', 'max:100'],
            'type' => ['required', Rule::in(['article', 'seo'])],
            'tone' => ['required', Rule::in(['professional', 'casual', 'luxury'])],
            'length' => ['required', Rule::in(['short', 'medium', 'long'])],
            'with_images' => ['sometimes', 'boolean'],
            'image_count' => ['required_if:with_images,true', 'integer', 'min:1', 'max:4'],
            'auto_publish' => ['sometimes', 'boolean'],
            'category_id' => ['nullable', 'integer', 'exists:post_categories,id'],
            'scheduled_at' => ['required', 'date'],
        ];
    }
}
