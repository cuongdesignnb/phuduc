<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->is_admin; }
    protected function prepareForValidation(): void { $this->merge(['title' => trim((string) $this->input('title')), 'slug' => trim((string) $this->input('slug', ''))]); }
    public function rules(): array { return ['title' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', 'unique:posts,slug'], 'post_category_id' => ['nullable', 'integer', 'exists:post_categories,id'], 'summary' => ['nullable', 'string', 'max:5000'], 'content' => ['nullable', 'string', 'max:100000'], 'status' => ['required', Rule::in(['draft', 'published'])], 'featured_media_id' => ['nullable', 'integer', 'exists:media_libraries,id']]; }
}
