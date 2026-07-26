<?php

namespace App\Http\Requests\Admin;

use App\Services\Admin\Operations\OrderStatusRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderIndexRequest extends FormRequest
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
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(OrderStatusRegistry::values())],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'sort' => ['nullable', Rule::in(['latest', 'oldest', 'total_high', 'total_low'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
