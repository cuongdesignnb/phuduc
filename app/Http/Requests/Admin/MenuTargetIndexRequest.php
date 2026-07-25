<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MenuTargetIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    protected function prepareForValidation(): void
    {
        $ids = $this->input('ids', []);
        if (! is_array($ids)) {
            $ids = $ids === '' ? [] : explode(',', (string) $ids);
        }

        $this->merge([
            'search' => trim((string) $this->input('search', '')),
            'ids' => array_values(array_filter(array_map('intval', $ids))),
            'limit' => min(20, max(1, (int) $this->input('limit', 20))),
        ]);
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:100'], 'ids' => ['array'], 'ids.*' => ['integer', 'min:1'], 'limit' => ['integer', 'min:1', 'max:20']];
    }
}
