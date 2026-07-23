<?php

namespace App\Services\Storefront;

use Illuminate\Support\Str;

class CheckoutIntentService
{
    public const SESSION_KEY = 'checkout_intent';

    public function issue(): string
    {
        $intent = session(self::SESSION_KEY);
        if (is_string($intent) && strlen($intent) >= 32) {
            return $intent;
        }

        $intent = Str::random(64);
        session()->put(self::SESSION_KEY, $intent);

        return $intent;
    }

    public function current(): ?string
    {
        $intent = session(self::SESSION_KEY);

        return is_string($intent) ? $intent : null;
    }
}
