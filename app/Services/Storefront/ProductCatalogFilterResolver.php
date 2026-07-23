<?php

namespace App\Services\Storefront;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductCatalogFilterResolver
{
    private const MAX_PRICE = 1000000000000;

    private const SORT_OPTIONS = ['latest', 'price_asc', 'price_desc', 'name_asc', 'name_desc'];

    /**
     * @return array{
     *     display_filters: array{search: ?string, min_price: mixed, max_price: mixed, sort: string},
     *     query_filters: array{search: ?string, min_price: ?float, max_price: ?float, sort: string},
     *     errors: array<string, array<int, string>>
     * }
     */
    public function resolve(Request $request): array
    {
        $displayFilters = $this->displayFilters($request);

        $validator = Validator::make($request->query(), $this->rules());
        $validator->after(function ($validator) use ($displayFilters): void {
            if (
                is_numeric($displayFilters['min_price'])
                && is_numeric($displayFilters['max_price'])
                && (float) $displayFilters['min_price'] > (float) $displayFilters['max_price']
            ) {
                $validator->errors()->add('min_price', 'Giá tối thiểu phải nhỏ hơn hoặc bằng giá tối đa.');
            }
        });

        $validator->passes();
        $errors = $validator->errors()->toArray();

        return [
            'display_filters' => $displayFilters,
            'query_filters' => $this->queryFilters($displayFilters, $errors),
            'errors' => $errors,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'min:0', 'max:'.self::MAX_PRICE],
            'max_price' => ['nullable', 'numeric', 'min:0', 'max:'.self::MAX_PRICE],
            'sort' => ['nullable', 'in:'.implode(',', self::SORT_OPTIONS)],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array{search: ?string, min_price: mixed, max_price: mixed, sort: string}
     */
    private function displayFilters(Request $request): array
    {
        return [
            'search' => $request->query('search') !== null ? (string) $request->query('search') : null,
            'min_price' => $request->query('min_price') !== null ? $request->query('min_price') : null,
            'max_price' => $request->query('max_price') !== null ? $request->query('max_price') : null,
            'sort' => $request->query('sort') !== null ? (string) $request->query('sort') : 'latest',
        ];
    }

    /**
     * @param  array{search: ?string, min_price: mixed, max_price: mixed, sort: string}  $displayFilters
     * @param  array<string, array<int, string>>  $errors
     * @return array{search: ?string, min_price: ?float, max_price: ?float, sort: string}
     */
    private function queryFilters(array $displayFilters, array $errors): array
    {
        $hasRangeError = isset($errors['min_price'])
            && in_array('Giá tối thiểu phải nhỏ hơn hoặc bằng giá tối đa.', $errors['min_price'], true);

        return [
            'search' => isset($errors['search']) || ! filled($displayFilters['search'])
                ? null
                : trim((string) $displayFilters['search']),
            'min_price' => isset($errors['min_price']) || $hasRangeError || ! is_numeric($displayFilters['min_price'])
                ? null
                : (float) $displayFilters['min_price'],
            'max_price' => isset($errors['max_price']) || $hasRangeError || ! is_numeric($displayFilters['max_price'])
                ? null
                : (float) $displayFilters['max_price'],
            'sort' => isset($errors['sort']) || ! in_array($displayFilters['sort'], self::SORT_OPTIONS, true)
                ? 'latest'
                : $displayFilters['sort'],
        ];
    }
}
