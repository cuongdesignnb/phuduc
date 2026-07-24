<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdatePostRequest extends StorePostRequest
{
    public function rules(): array
    {
        $post = $this->route('post');
        $rules = parent::rules();
        $rules['slug'] = ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('posts', 'slug')->ignore($post?->id)];
        $rules['version'] = ['nullable', 'string', 'max:100'];
        return $rules;
    }
}
