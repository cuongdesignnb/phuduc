<?php

namespace App\Services\Admin;

use Illuminate\Validation\ValidationException;

class AdminConcurrencyService
{
    public function assertVersion(?string $submitted, mixed $current, string $message): void
    {
        if ($submitted !== null && $submitted !== $this->version($current)) {
            throw ValidationException::withMessages(['version' => $message]);
        }
    }

    public function assertFingerprint(?string $submitted, string $current, string $message): void
    {
        if ($submitted !== null && ! hash_equals($current, $submitted)) {
            throw ValidationException::withMessages(['version' => $message]);
        }
    }

    public function version(mixed $model): string
    {
        return (string) optional($model->updated_at)->toISOString();
    }

    public function fingerprint(iterable $models): string
    {
        return sha1(collect($models)->map(fn ($model) => $this->version($model))->implode('|'));
    }
}
