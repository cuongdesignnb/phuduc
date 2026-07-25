<?php

namespace App\Http\Requests\Admin;

use App\Services\Admin\Operations\OrderStatusRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['reason' => trim((string) $this->input('reason', ''))]);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(OrderStatusRegistry::values())],
            'version' => ['required', 'string', 'max:100'],
            'reason' => ['nullable', 'string', 'max:500', 'required_if:status,cancelled'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Vui lòng chọn trạng thái đơn hàng.',
            'version.required' => 'Phiên dữ liệu đơn hàng không hợp lệ. Vui lòng tải lại trang.',
            'reason.required_if' => 'Vui lòng nhập lý do hủy đơn hàng.',
            'reason.max' => 'Lý do hủy không được vượt quá 500 ký tự.',
        ];
    }
}
