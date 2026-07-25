<?php

namespace App\Http\Requests\Admin;

use App\Services\Admin\Content\MenuTreeValidator;
use Illuminate\Foundation\Http\FormRequest;

class SaveMenuItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'items' => ['present', 'array'],
            'version' => ['required', 'string', 'max:100'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            app(MenuTreeValidator::class)->validate($this->input('items', []), $validator);
        });
    }
}
