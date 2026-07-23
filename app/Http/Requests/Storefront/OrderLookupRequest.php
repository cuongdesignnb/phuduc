<?php

namespace App\Http\Requests\Storefront;

use App\Services\Storefront\PhoneNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class OrderLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'order_number' => strtoupper(trim((string) $this->input('order_number'))),
            'customer_phone' => app(PhoneNormalizer::class)->normalize($this->input('customer_phone')),
        ]);
    }

    public function rules(): array
    {
        return ['order_number' => ['required', 'string', 'max:64'], 'customer_phone' => ['required', 'string', 'regex:/^0\d{9}$/']];
    }

    public function messages(): array
    {
        return [
            'order_number.required' => 'Vui lòng nhập mã đơn hàng.',
            'customer_phone.required' => 'Vui lòng nhập số điện thoại.',
            'customer_phone.regex' => 'Số điện thoại không đúng định dạng.',
        ];
    }
}
