<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class WarrantyOrderLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['search' => trim((string) $this->input('search', '')), 'ids' => array_map('intval', (array) $this->input('ids', []))]);
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:100'], 'ids' => ['nullable', 'array', 'max:20'], 'ids.*' => ['integer', 'min:1'], 'limit' => ['nullable', 'integer', 'min:1', 'max:20']];
    }
}
