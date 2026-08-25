<?php

namespace App\Http\Requests\Admin;

use App\Rules\MediaAssetRule;
use Illuminate\Foundation\Http\FormRequest;

class AttachProductMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'media_id' => ['nullable', 'integer', MediaAssetRule::image()],
            'media_ids' => ['nullable', 'array', 'min:1', 'max:20'],
            'media_ids.*' => ['integer', 'distinct', MediaAssetRule::image()],
            'is_360' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->filled('media_id') && $this->input('media_ids', []) === []) {
                $validator->errors()->add('media_id', 'Vui lòng chọn ít nhất một ảnh.');
            }
        });
    }
}
