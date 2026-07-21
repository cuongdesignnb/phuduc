<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.home-content.index'))->assertRedirect(route('login'));
    }

    public function test_normal_user_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.home-content.index'))->assertForbidden();
    }

    public function test_admin_can_open_home_content(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.home-content.index'))->assertOk();
    }
}
