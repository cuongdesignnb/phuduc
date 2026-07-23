<?php

namespace App\Http\Requests\Storefront;

use App\Services\Storefront\PhoneNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'checkout_intent' => trim((string) $this->input('checkout_intent')),
            'customer_name' => trim((string) $this->input('customer_name')),
            'customer_phone' => app(PhoneNormalizer::class)->normalize($this->input('customer_phone')),
            'customer_email' => filled($this->input('customer_email')) ? strtolower(trim((string) $this->input('customer_email'))) : null,
            'shipping_address' => trim((string) $this->input('shipping_address')),
            'notes' => filled($this->input('notes')) ? trim((string) $this->input('notes')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'checkout_intent' => ['required', 'string', 'max:128'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^0\d{9}$/'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'shipping_address' => ['required', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'checkout_intent.required' => 'Phiên thanh toán không hợp lệ. Vui lòng tải lại trang.',
            'customer_name.required' => 'Vui lòng nhập họ tên.',
            'customer_phone.required' => 'Vui lòng nhập số điện thoại.',
            'customer_phone.regex' => 'Số điện thoại không đúng định dạng.',
            'customer_email.email' => 'Email không đúng định dạng.',
            'shipping_address.required' => 'Vui lòng nhập địa chỉ giao hàng.',
        ];
    }
}
