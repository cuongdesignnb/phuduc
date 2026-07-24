<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SaveSettingsRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->is_admin; }
    public function rules(): array { return ['settings' => ['required', 'array', 'max:100'], 'settings.*.key' => ['required', 'string', 'max:100'], 'settings.*.value' => ['nullable', 'string'], 'settings.*.media_id' => ['nullable', 'integer', 'exists:media_libraries,id'], 'version' => ['nullable', 'string', 'max:100']]; }
}
