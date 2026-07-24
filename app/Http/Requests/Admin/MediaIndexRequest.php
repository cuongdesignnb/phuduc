<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MediaIndexRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->is_admin; }

    protected function prepareForValidation(): void
    {
        $this->merge(['search' => trim((string) $this->input('search', ''))]);
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:100'], 'media_type' => ['nullable', Rule::in(['image', 'file'])]];
    }
}
