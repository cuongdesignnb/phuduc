<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class NewsIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:150'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array{search: ?string, category: ?string}
     */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'search' => filled($validated['search'] ?? null) ? trim((string) $validated['search']) : null,
            'category' => filled($validated['category'] ?? null) ? trim((string) $validated['category']) : null,
        ];
    }
}
