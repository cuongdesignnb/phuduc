<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UploadProductImagesRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->is_admin; }
    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'max:20'],
            'images.*' => ['required', 'file', 'image', 'max:10240', 'mimetypes:image/jpeg,image/png,image/webp,image/gif'],
            'is_360' => ['nullable', 'boolean'],
        ];
    }
}
