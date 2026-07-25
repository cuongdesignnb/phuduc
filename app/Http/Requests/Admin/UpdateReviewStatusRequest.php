<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReviewStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'version' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Vui lòng chọn trạng thái đánh giá.',
            'version.required' => 'Phiên đánh giá không hợp lệ. Vui lòng tải lại trang.',
        ];
    }
}
