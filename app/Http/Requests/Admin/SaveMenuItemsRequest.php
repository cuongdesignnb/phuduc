<?php

namespace App\Http\Requests\Admin;

use App\Services\Admin\Content\MenuTargetRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveMenuItemsRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->is_admin; }
    public function rules(): array
    {
        return ['items' => ['present', 'array'], 'items.*.id' => ['nullable', 'integer'], 'items.*.client_key' => ['nullable', 'string', 'max:100'], 'items.*.title' => ['required', 'string', 'max:255'], 'items.*.url' => ['nullable', 'string', 'max:500'], 'items.*.model_type' => ['required', Rule::in(MenuTargetRegistry::keys())], 'items.*.model_id' => ['nullable', 'integer'], 'items.*.children' => ['present', 'array'], 'version' => ['nullable', 'string', 'max:100']];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $nodes = 0;
            $walk = function (array $items, int $depth) use (&$walk, &$nodes, $validator): void {
                if ($depth > 4) { $validator->errors()->add('items', 'Menu chỉ hỗ trợ tối đa 4 cấp.'); return; }
                foreach ($items as $item) { $nodes++; if ($nodes > 100) { $validator->errors()->add('items', 'Menu không được vượt quá 100 mục.'); return; } $walk($item['children'] ?? [], $depth + 1); }
            };
            $walk($this->input('items', []), 1);
        });
    }
}
