<?php

namespace App\Services\Storefront;

use Illuminate\Support\Str;

class CheckoutIntentService
{
    public const SESSION_KEY = 'checkout_intent';

    public const CONSUMED_TOKEN_KEY = 'checkout_intent_consumed_order_token';

    public function issue(): string
    {
        $intent = session(self::SESSION_KEY);
        if (is_string($intent) && strlen($intent) >= 32 && ! session()->has(self::CONSUMED_TOKEN_KEY)) {
            return $intent;
        }

        return $this->rotate();
    }

    public function rotate(): string
    {
        $intent = Str::random(64);
        session()->put(self::SESSION_KEY, $intent);
        session()->forget(self::CONSUMED_TOKEN_KEY);

        return $intent;
    }

    public function current(): ?string
    {
        $intent = session(self::SESSION_KEY);

        return is_string($intent) ? $intent : null;
    }

    public function isActive(string $intent): bool
    {
        return hash_equals((string) $this->current(), $intent)
            && ! session()->has(self::CONSUMED_TOKEN_KEY);
    }

    public function matchesCurrent(string $intent): bool
    {
        $current = $this->current();

        return is_string($current) && hash_equals($current, $intent);
    }

    public function consume(string $intent, string $publicToken): void
    {
        if ($this->current() === $intent) {
            session()->put(self::CONSUMED_TOKEN_KEY, $publicToken);
        }
    }

    public function consumedOrderToken(): ?string
    {
        $token = session(self::CONSUMED_TOKEN_KEY);

        return is_string($token) ? $token : null;
    }
}
