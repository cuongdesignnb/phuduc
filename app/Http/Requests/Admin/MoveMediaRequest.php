<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MoveMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'media_ids' => ['required', 'array', 'min:1', 'max:100'],
            'media_ids.*' => ['integer', 'distinct', 'exists:media_libraries,id'],
            'folder_id' => ['nullable', 'integer', 'exists:media_folders,id'],
        ];
    }
}
