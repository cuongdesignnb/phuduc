<?php

namespace App\Http\Requests\Admin;

use App\Rules\MediaAssetRule;
use Illuminate\Foundation\Http\FormRequest;

class AttachProductMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return ['media_id' => ['required', 'integer', MediaAssetRule::image()], 'is_360' => ['nullable', 'boolean']];
    }
}
