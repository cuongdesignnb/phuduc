<?php

namespace App\Http\Requests\Admin;

use App\Services\Admin\Content\MenuLocationRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->is_admin; }
    public function rules(): array { return ['name' => ['required', 'string', 'max:255'], 'location' => ['required', Rule::in(MenuLocationRegistry::keys()), 'unique:menus,location']]; }
}
