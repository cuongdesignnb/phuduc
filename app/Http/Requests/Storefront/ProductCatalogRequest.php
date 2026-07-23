<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ProductCatalogRequest extends FormRequest
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
            'min_price' => ['nullable', 'numeric', 'min:0', 'max:1000000000000'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'max:1000000000000'],
            'sort' => ['nullable', 'in:latest,price_asc,price_desc,name_asc,name_desc'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $min = $this->input('min_price');
                $max = $this->input('max_price');

                if ($min !== null && $max !== null && (float) $min > (float) $max) {
                    $validator->errors()->add('min_price', 'Minimum price must be less than or equal to maximum price.');
                }
            },
        ];
    }

    /**
     * @return array{search: ?string, min_price: ?float, max_price: ?float, sort: string}
     */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'search' => filled($validated['search'] ?? null) ? trim((string) $validated['search']) : null,
            'min_price' => $validated['min_price'] ?? null,
            'max_price' => $validated['max_price'] ?? null,
            'sort' => $validated['sort'] ?? 'latest',
        ];
    }
}
