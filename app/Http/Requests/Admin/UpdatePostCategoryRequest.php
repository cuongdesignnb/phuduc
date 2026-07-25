<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdatePostCategoryRequest extends StorePostCategoryRequest
{
    public function rules(): array
    {
        $category = $this->route('post_category');
        $rules = parent::rules();
        $rules['slug'] = ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('post_categories', 'slug')->ignore($category?->id)];
        return $rules;
    }
}
