<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePostCategoryRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->is_admin; }
    public function rules(): array { return ['name' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', 'unique:post_categories,slug'], 'parent_id' => ['nullable', 'integer', 'exists:post_categories,id'], 'description' => ['nullable', 'string', 'max:5000']]; }
}
