<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class RemoveCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['product_id' => ['required', 'integer', 'min:1']];
    }

    public function messages(): array
    {
        return ['product_id.required' => 'Vui lòng chọn sản phẩm.'];
    }
}
