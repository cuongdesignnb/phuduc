<?php

namespace App\Http\Requests\Admin;

use App\Services\Admin\Content\MenuLocationRegistry;
use Illuminate\Validation\Rule;

class UpdateMenuRequest extends StoreMenuRequest
{
    public function rules(): array
    {
        $menu = $this->route('menu');
        $rules = parent::rules();
        $rules['location'] = ['required', Rule::in(MenuLocationRegistry::keys()), Rule::unique('menus', 'location')->ignore($menu?->id)];
        return $rules;
    }
}
