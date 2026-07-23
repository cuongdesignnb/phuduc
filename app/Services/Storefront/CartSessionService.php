<?php

namespace App\Services\Storefront;

use Illuminate\Support\Arr;

class CartSessionService
{
    public const SESSION_KEY = 'cart';

    /** @return array<int, array{quantity: int}> */
    public function normalize(): array
    {
        $raw = session()->get(self::SESSION_KEY, []);
        $normalized = [];

        foreach (is_array($raw) ? $raw : [] as $key => $value) {
            $productId = filter_var($key, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $quantity = is_array($value) ? Arr::get($value, 'quantity') : $value;

            if ($productId === false || filter_var($quantity, FILTER_VALIDATE_INT) === false || (int) $quantity < 1) {
                continue;
            }

            $normalized[(int) $productId] = ['quantity' => min(99, (int) $quantity)];
        }

        ksort($normalized);
        session()->put(self::SESSION_KEY, $normalized);

        return $normalized;
    }

    /** @param array<int, array{quantity: int}> $cart */
    public function put(array $cart): void
    {
        $normalized = [];

        foreach ($cart as $productId => $entry) {
            $id = filter_var($productId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $quantity = $entry['quantity'] ?? null;

            if ($id !== false && filter_var($quantity, FILTER_VALIDATE_INT) !== false && (int) $quantity > 0) {
                $normalized[(int) $id] = ['quantity' => min(99, (int) $quantity)];
            }
        }

        ksort($normalized);
        session()->put(self::SESSION_KEY, $normalized);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function count(): int
    {
        return collect($this->normalize())->sum('quantity');
    }
}
