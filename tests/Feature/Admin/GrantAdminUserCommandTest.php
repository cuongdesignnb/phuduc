<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class GrantAdminUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_grant_admin_command_promotes_exact_existing_user(): void
    {
        $user = User::factory()->create(['email' => 'Admin.Example@example.com']);

        $exitCode = Artisan::call('user:grant-admin', [
            'email' => ' ADMIN.EXAMPLE@EXAMPLE.COM ',
            '--no-interaction' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertTrue($user->fresh()->is_admin);
        $this->assertStringContainsString(
            'Admin access granted to admin.example@example.com.',
            Artisan::output()
        );
    }

    public function test_grant_admin_command_is_idempotent(): void
    {
        $user = User::factory()->admin()->create(['email' => 'admin@example.com']);

        $exitCode = Artisan::call('user:grant-admin', [
            'email' => $user->email,
            '--no-interaction' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertTrue($user->fresh()->is_admin);
        $this->assertStringContainsString(
            'User admin@example.com already has Admin access.',
            Artisan::output()
        );
    }

    public function test_grant_admin_command_fails_for_unknown_user(): void
    {
        User::factory()->create();
        $before = User::query()->count();

        $exitCode = Artisan::call('user:grant-admin', [
            'email' => 'missing@example.com',
            '--no-interaction' => true,
        ]);

        $this->assertNotSame(0, $exitCode);
        $this->assertSame($before, User::query()->count());
        $this->assertDatabaseMissing('users', ['email' => 'missing@example.com']);
    }

    public function test_grant_admin_command_does_not_promote_other_users(): void
    {
        $target = User::factory()->create(['email' => 'target@example.com']);
        $other = User::factory()->create(['email' => 'other@example.com']);

        Artisan::call('user:grant-admin', [
            'email' => $target->email,
            '--no-interaction' => true,
        ]);

        $this->assertTrue($target->fresh()->is_admin);
        $this->assertFalse($other->fresh()->is_admin);
    }

    public function test_grant_admin_command_does_not_create_or_change_credentials(): void
    {
        $user = User::factory()->create(['email' => 'safe@example.com']);
        $password = $user->password;

        Artisan::call('user:grant-admin', [
            'email' => $user->email,
            '--no-interaction' => true,
        ]);

        $this->assertDatabaseCount('users', 1);
        $this->assertSame($password, $user->fresh()->password);
    }

    public function test_granted_admin_can_access_admin_while_normal_user_remains_forbidden(): void
    {
        $target = User::factory()->create(['email' => 'production.owner@example.com']);
        $normal = User::factory()->create(['email' => 'normal@example.com']);

        Artisan::call('user:grant-admin', [
            'email' => $target->email,
            '--no-interaction' => true,
        ]);

        $this->actingAs($target->fresh())->get(route('admin.home-content.index'))->assertOk();
        $this->actingAs($normal)->get(route('admin.home-content.index'))->assertForbidden();
    }
}
