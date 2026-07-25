<?php

namespace Tests\Feature\Admin;

use App\Models\User;

trait Pr3bTestHelpers
{
    protected function admin(): User
    {
        return User::factory()->admin()->create();
    }

    protected function assertAdminPage($response, string $type): void
    {
        $response->assertOk()->assertInertia(fn ($page) => $page->where('page.type', $type)->has('page.meta')->has('page.admin')->has('page.module'));
    }
}
