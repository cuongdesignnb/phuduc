<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->is_admin; }

    protected function prepareForValidation(): void
    {
        if (! $this->has('files') && $this->hasFile('file')) {
            $this->merge(['files' => [$this->file('file')]]);
        }
    }

    public function rules(): array
    {
        return [
            'files' => ['required', 'array', 'max:20'],
            'files.*' => ['required', 'file', 'max:10240', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,application/pdf'],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $total = collect($this->file('files', []))->sum(fn ($file) => (int) $file->getSize());
            if ($total > 50 * 1024 * 1024) {
                $validator->errors()->add('files', 'Tổng dung lượng tệp không được vượt quá 50 MB.');
            }
        });
    }
}
