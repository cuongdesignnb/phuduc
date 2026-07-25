<?php

namespace App\Http\Requests\Admin;

use App\Services\Storefront\PhoneNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWarrantyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['serial_number' => trim((string) $this->input('serial_number')), 'product_name' => trim((string) $this->input('product_name')), 'customer_name' => trim((string) $this->input('customer_name')), 'customer_phone' => app(PhoneNormalizer::class)->normalize($this->input('customer_phone'))]);
    }

    public function rules(): array
    {
        return ['mode' => ['required', Rule::in(['order', 'manual'])], 'order_id' => ['nullable', 'integer', 'exists:orders,id', 'required_if:mode,order'], 'order_item_id' => ['nullable', 'integer', 'exists:order_items,id', 'required_if:mode,order'], 'serial_number' => ['required', 'string', 'max:255'], 'product_name' => ['nullable', 'string', 'max:255', 'required_if:mode,manual'], 'customer_name' => ['nullable', 'string', 'max:255', 'required_if:mode,manual'], 'customer_phone' => ['nullable', 'string', 'regex:/^0\d{9}$/', 'required_if:mode,manual'], 'activation_date' => ['nullable', 'date'], 'expiration_date' => ['nullable', 'date', 'after_or_equal:activation_date']];
    }

    public function messages(): array
    {
        return ['mode.required' => 'Vui lòng chọn nguồn bảo hành.', 'order_item_id.required_if' => 'Vui lòng chọn sản phẩm trong đơn hàng.', 'product_name.required_if' => 'Vui lòng nhập tên sản phẩm.', 'customer_name.required_if' => 'Vui lòng nhập tên khách hàng.', 'customer_phone.required_if' => 'Vui lòng nhập số điện thoại khách hàng.', 'customer_phone.regex' => 'Số điện thoại không đúng định dạng.', 'expiration_date.after_or_equal' => 'Ngày hết hạn phải từ ngày kích hoạt trở đi.'];
    }
}
