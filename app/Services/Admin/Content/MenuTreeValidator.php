<?php

namespace App\Services\Admin\Content;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;

final class MenuTreeValidator
{
    private const ALLOWED_KEYS = ['id', 'client_key', 'title', 'url', 'model_type', 'model_id', 'children'];

    public function __construct(private readonly AdminUrlService $urls) {}

    public function validate(mixed $items, ValidatorContract $validator): void
    {
        if (! is_array($items)) {
            return;
        }

        $ids = [];
        $clientKeys = [];
        $nodes = 0;
        $this->walk($items, 'items', 1, $nodes, $ids, $clientKeys, $validator);
    }

    private function walk(array $items, string $path, int $depth, int &$nodes, array &$ids, array &$clientKeys, ValidatorContract $errors): void
    {
        if ($depth > 4) {
            $errors->errors()->add($path, 'Menu chỉ được phép tối đa 4 cấp.');

            return;
        }

        foreach (array_values($items) as $index => $item) {
            $nodePath = $path.'.'.$index;
            $nodes++;
            if ($nodes > 100) {
                $errors->errors()->add('items', 'Menu không được vượt quá 100 mục.');

                return;
            }
            if (! is_array($item)) {
                $errors->errors()->add($nodePath, 'Mục menu không hợp lệ.');

                continue;
            }

            $unknown = array_diff(array_keys($item), self::ALLOWED_KEYS);
            if ($unknown !== []) {
                $errors->errors()->add($nodePath, 'Mục menu chứa trường không được phép.');
            }

            $fieldValidator = Validator::make(
                $item,
                [
                    'id' => ['nullable', 'integer'],
                    'client_key' => ['required', 'string', 'max:100'],
                    'title' => ['required', 'string', 'max:255'],
                    'url' => ['nullable', 'string', 'max:500'],
                    'model_type' => ['required', 'string'],
                    'model_id' => ['nullable', 'integer', 'min:1'],
                    'children' => ['required', 'array'],
                ],
                [
                    'id.integer' => ':attribute phải là số nguyên.',
                    'client_key.required' => 'Mục menu thiếu khóa tạm.',
                    'client_key.string' => ':attribute phải là chuỗi.',
                    'client_key.max' => ':attribute không được vượt quá :max ký tự.',
                    'title.required' => 'Tên mục menu là bắt buộc.',
                    'title.string' => 'Tên mục menu phải là chuỗi.',
                    'title.max' => 'Tên mục menu không được vượt quá :max ký tự.',
                    'url.string' => ':attribute phải là chuỗi.',
                    'url.max' => ':attribute không được vượt quá :max ký tự.',
                    'model_type.required' => 'Loại đích menu là bắt buộc.',
                    'model_type.string' => ':attribute phải là chuỗi.',
                    'model_id.integer' => 'ID đích menu phải là số nguyên.',
                    'model_id.min' => 'ID đích menu phải lớn hơn hoặc bằng :min.',
                    'children.required' => 'Danh sách mục con là bắt buộc.',
                    'children.array' => 'Danh sách mục con không hợp lệ.',
                ],
                [
                    'id' => 'ID mục menu',
                    'client_key' => 'khóa tạm mục menu',
                    'title' => 'tên mục menu',
                    'url' => 'URL menu',
                    'model_type' => 'loại đích menu',
                    'model_id' => 'ID đích menu',
                    'children' => 'danh sách mục con',
                ],
            );
            foreach ($fieldValidator->errors()->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $errors->errors()->add($nodePath.'.'.$field, $message);
                }
            }

            if (array_key_exists('id', $item) && $item['id'] !== null && ! is_int($item['id'])) {
                $errors->errors()->add($nodePath.'.id', 'ID mục menu phải là số nguyên.');
            } elseif (isset($item['id'])) {
                if (isset($ids[$item['id']])) {
                    $errors->errors()->add($nodePath.'.id', 'ID mục menu không được trùng.');
                }
                $ids[$item['id']] = $nodePath;
            }

            if (array_key_exists('client_key', $item) && $item['client_key'] !== null) {
                if (isset($clientKeys[$item['client_key']])) {
                    $errors->errors()->add($nodePath.'.client_key', 'Khóa tạm mục menu không được trùng.');
                }
                $clientKeys[$item['client_key']] = $nodePath;
            }

            $type = $item['model_type'] ?? null;
            if (! in_array($type, MenuTargetRegistry::keys(), true)) {
                $errors->errors()->add($nodePath.'.model_type', 'Loại đích menu không hợp lệ.');
            } elseif ($type === 'url') {
                try {
                    $this->urls->normalize($item['url'] ?? null);
                } catch (\Illuminate\Validation\ValidationException $exception) {
                    $errors->errors()->add($nodePath.'.url', $exception->errors()['items'][0] ?? 'URL menu không an toàn.');
                }
            }

            if (isset($item['children']) && is_array($item['children'])) {
                $this->walk($item['children'], $nodePath.'.children', $depth + 1, $nodes, $ids, $clientKeys, $errors);
            }
        }
    }
}
