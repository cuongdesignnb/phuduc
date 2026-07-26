<?php

namespace App\Http\Requests\Admin;

class UpdateWarrantyRequest extends StoreWarrantyRequest
{
    public function rules(): array
    {
        return [...parent::rules(), 'version' => ['required', 'string', 'max:100']];
    }

    public function messages(): array
    {
        return [...parent::messages(), 'version.required' => 'Phiên bảo hành không hợp lệ. Vui lòng tải lại trang.'];
    }
}
