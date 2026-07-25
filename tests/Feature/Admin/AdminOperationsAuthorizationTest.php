<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOperationsAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_operations_or_lookup_endpoints(): void
    {
        $this->get(route('admin.orders.index'))->assertRedirect(route('login'));
        $this->get(route('admin.reviews.index'))->assertRedirect(route('login'));
        $this->get(route('admin.warranties.index'))->assertRedirect(route('login'));
        $this->get(route('admin.warranty-lookups.orders'))->assertRedirect(route('login'));
    }
}
