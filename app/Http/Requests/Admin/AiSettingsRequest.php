<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AiSettingsRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->is_admin; }

    public function rules(): array
    {
        return [
            'content_api_key' => ['nullable', 'string', 'max:500'],
            'image_api_key' => ['nullable', 'string', 'max:500'],
            'content_base_url' => ['required', 'url:https', 'max:500'],
            'image_base_url' => ['required', 'url:https', 'max:500'],
            'content_wire_api' => ['required', Rule::in(['chat_completions', 'responses'])],
            'content_model' => ['required', 'string', 'max:150'],
            'content_max_tokens' => ['required', 'integer', 'min:256', 'max:12000'],
            'image_model' => ['required', 'string', 'max:150'],
            'image_quality' => ['required', Rule::in(['low', 'medium', 'high'])],
            'content_clear_api_key' => ['sometimes', 'boolean'],
            'image_clear_api_key' => ['sometimes', 'boolean'],
        ];
    }
}
