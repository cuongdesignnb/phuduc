<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostIndexRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->is_admin; }
    protected function prepareForValidation(): void { $this->merge(['search' => trim((string) $this->input('search', ''))]); }
    public function rules(): array { return ['search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', Rule::in(['draft', 'published'])], 'page' => ['nullable', 'integer', 'min:1']]; }
}
