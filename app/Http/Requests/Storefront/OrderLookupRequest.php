<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class OrderLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['order_number' => ['required', 'string', 'max:64'], 'customer_phone' => ['required', 'string', 'max:20']];
    }

    public function messages(): array
    {
        return ['order_number.required' => 'Vui lòng nhập mã đơn hàng.', 'customer_phone.required' => 'Vui lòng nhập số điện thoại.'];
    }
}
