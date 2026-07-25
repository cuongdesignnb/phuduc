<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateProductRequest extends StoreProductRequest
{
    public function rules(): array
    {
        $product = $this->route('product');
        $rules = parent::rules();
        $rules['slug'] = ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('products', 'slug')->ignore($product?->id)];
        $rules['version'] = ['required', 'string', 'max:100'];

        return $rules;
    }
}
