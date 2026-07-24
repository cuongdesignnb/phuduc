<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReorderProductImagesRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->is_admin; }
    public function rules(): array
    {
        return ['order' => ['required', 'array', 'min:1'], 'order.*' => ['required', 'integer', 'distinct', 'exists:product_images,id']];
    }
}
