<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachProductMediaRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->is_admin; }
    public function rules(): array
    {
        return ['media_id' => ['required', 'integer', 'exists:media_libraries,id'], 'is_360' => ['nullable', 'boolean']];
    }
}
