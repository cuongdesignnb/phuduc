<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AiCreatePostRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->is_admin; }

    public function rules(): array
    {
        return ['auto_publish' => ['sometimes', 'boolean']];
    }
}
