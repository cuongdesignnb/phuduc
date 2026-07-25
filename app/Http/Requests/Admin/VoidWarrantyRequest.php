<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class VoidWarrantyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return ['version' => ['required', 'string', 'max:100'], 'reason' => ['required', 'string', 'max:500']];
    }

    public function messages(): array
    {
        return ['version.required' => 'Phiên bảo hành không hợp lệ. Vui lòng tải lại trang.', 'reason.required' => 'Vui lòng nhập lý do hủy bảo hành.', 'reason.max' => 'Lý do hủy không được vượt quá 500 ký tự.'];
    }
}
