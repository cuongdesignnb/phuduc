<?php

namespace App\Http\Requests\Storefront;

use App\Services\Storefront\PhoneNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class WarrantyLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'serial_number' => strtoupper(trim((string) $this->input('serial_number'))),
            'customer_phone' => app(PhoneNormalizer::class)->normalize($this->input('customer_phone')),
        ]);
    }

    public function rules(): array
    {
        return ['serial_number' => ['required', 'string', 'max:255'], 'customer_phone' => ['required', 'string', 'regex:/^0\d{9}$/']];
    }

    public function messages(): array
    {
        return [
            'serial_number.required' => 'Vui lòng nhập mã bảo hành.',
            'customer_phone.required' => 'Vui lòng nhập số điện thoại.',
            'customer_phone.regex' => 'Số điện thoại không đúng định dạng.',
        ];
    }
}
