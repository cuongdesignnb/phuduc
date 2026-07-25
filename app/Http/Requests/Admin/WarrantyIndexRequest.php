<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarrantyIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['search' => trim((string) $this->input('search', ''))]);
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:100'], 'effective_status' => ['nullable', Rule::in(['scheduled', 'active', 'expired', 'voided'])], 'mode' => ['nullable', Rule::in(['order', 'manual'])], 'expiring_within' => ['nullable', Rule::in(['30'])], 'page' => ['nullable', 'integer', 'min:1']];
    }
}
