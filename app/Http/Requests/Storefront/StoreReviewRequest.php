<?php

namespace App\Http\Requests\Storefront;

use App\Services\Storefront\PhoneNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $content = trim(strip_tags((string) $this->input('content')));
        $content = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $content) ?? $content);
        $this->merge([
            'product_id' => (int) $this->input('product_id'),
            'customer_name' => trim((string) $this->input('customer_name')),
            'customer_phone' => app(PhoneNormalizer::class)->normalize($this->input('customer_phone')),
            'customer_email' => strtolower(trim((string) $this->input('customer_email'))),
            'content' => $content,
            'rating' => (int) $this->input('rating'),
        ]);
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where(fn ($query) => $query->where('status', 'active'))],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'regex:/^0\d{9}$/'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'content' => ['required', 'string', 'max:2000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.exists' => 'Sản phẩm không còn mở bán để nhận đánh giá.',
            'customer_name.required' => 'Vui lòng nhập tên của bạn.',
            'customer_phone.regex' => 'Số điện thoại không đúng định dạng.',
            'customer_email.email' => 'Email không đúng định dạng.',
            'content.required' => 'Vui lòng nhập nội dung đánh giá.',
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
        ];
    }
}
