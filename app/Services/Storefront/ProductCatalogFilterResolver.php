<?php

namespace App\Services\Storefront;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductCatalogFilterResolver
{
    private const MAX_PRICE = 1000000000000;

    private const RANGE_ERROR = 'Giá tối thiểu phải nhỏ hơn hoặc bằng giá tối đa.';

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

        $validator = Validator::make(
            $request->query(),
            $this->rules(),
            $this->messages(),
            $this->attributes(),
        );
        $validator->after(function ($validator) use ($displayFilters): void {
            if (
                is_numeric($displayFilters['min_price'])
                && is_numeric($displayFilters['max_price'])
                && (float) $displayFilters['min_price'] > (float) $displayFilters['max_price']
            ) {
                $validator->errors()->add('min_price', self::RANGE_ERROR);
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
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'search.string' => 'Từ khóa tìm kiếm phải là chuỗi.',
            'search.max' => 'Từ khóa tìm kiếm không được vượt quá 100 ký tự.',
            'min_price.numeric' => 'Giá từ phải là số.',
            'min_price.min' => 'Giá từ phải lớn hơn hoặc bằng 0.',
            'min_price.max' => 'Giá từ không được vượt quá 1.000.000.000.000.',
            'max_price.numeric' => 'Giá đến phải là số.',
            'max_price.min' => 'Giá đến phải lớn hơn hoặc bằng 0.',
            'max_price.max' => 'Giá đến không được vượt quá 1.000.000.000.000.',
            'sort.in' => 'Tùy chọn sắp xếp không hợp lệ.',
            'page.integer' => 'Số trang phải là số nguyên.',
            'page.min' => 'Số trang phải lớn hơn hoặc bằng 1.',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function attributes(): array
    {
        return [
            'search' => 'từ khóa tìm kiếm',
            'min_price' => 'giá từ',
            'max_price' => 'giá đến',
            'sort' => 'sắp xếp',
            'page' => 'số trang',
        ];
    }

    /**
     * @return array{search: ?string, min_price: mixed, max_price: mixed, sort: string}
     */
    private function displayFilters(Request $request): array
    {
        return [
            'search' => $this->scalarQueryValue($request, 'search'),
            'min_price' => $this->scalarQueryValue($request, 'min_price'),
            'max_price' => $this->scalarQueryValue($request, 'max_price'),
            'sort' => $this->scalarQueryValue($request, 'sort') ?? 'latest',
        ];
    }

    private function scalarQueryValue(Request $request, string $key): mixed
    {
        $value = $request->query($key);

        return is_scalar($value) ? (string) $value : null;
    }

    /**
     * @param  array{search: ?string, min_price: mixed, max_price: mixed, sort: string}  $displayFilters
     * @param  array<string, array<int, string>>  $errors
     * @return array{search: ?string, min_price: ?float, max_price: ?float, sort: string}
     */
    private function queryFilters(array $displayFilters, array $errors): array
    {
        $hasRangeError = isset($errors['min_price'])
            && in_array(self::RANGE_ERROR, $errors['min_price'], true);

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
