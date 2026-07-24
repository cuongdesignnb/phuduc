<?php

namespace Tests\Feature\Storefront;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommercePrivacyHeaderTest extends TestCase
{
    use RefreshDatabase;

    public function test_utility_pages_are_noindex_and_no_store(): void
    {
        foreach (['/gio-hang', '/tra-cuu-don-hang', '/tra-cuu-bao-hanh'] as $uri) {
            $this->get($uri)->assertOk()->assertHeader('Cache-Control', 'no-store, private')->assertHeader('Pragma', 'no-cache');
        }
    }
}
