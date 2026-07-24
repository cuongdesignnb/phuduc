<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['product_id' => ['required', 'integer', 'min:1'], 'quantity' => ['nullable', 'integer', 'min:1', 'max:99']];
    }

    public function messages(): array
    {
        return ['product_id.required' => 'Vui lòng chọn sản phẩm.', 'quantity.integer' => 'Số lượng phải là số nguyên.', 'quantity.min' => 'Số lượng tối thiểu là 1.', 'quantity.max' => 'Số lượng tối đa là 99.'];
    }
}
