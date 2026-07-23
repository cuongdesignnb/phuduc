<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['product_id' => ['required', 'integer', 'min:1'], 'quantity' => ['required', 'integer', 'min:0', 'max:99']];
    }

    public function messages(): array
    {
        return ['product_id.required' => 'Vui lòng chọn sản phẩm.', 'quantity.integer' => 'Số lượng phải là số nguyên.', 'quantity.min' => 'Số lượng không hợp lệ.', 'quantity.max' => 'Số lượng tối đa là 99.'];
    }
}
