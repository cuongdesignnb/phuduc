<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class WarrantyLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['serial_number' => ['required', 'string', 'max:255'], 'customer_phone' => ['required', 'string', 'max:20']];
    }

    public function messages(): array
    {
        return ['serial_number.required' => 'Vui lòng nhập mã bảo hành.', 'customer_phone.required' => 'Vui lòng nhập số điện thoại.'];
    }
}
