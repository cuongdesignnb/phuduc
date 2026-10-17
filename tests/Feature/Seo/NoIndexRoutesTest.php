<?php

namespace Tests\Feature\Seo;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoIndexRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_and_commerce_utility_pages_send_noindex_http_headers(): void
    {
        $this->get('/login')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get('/gio-hang')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get('/thanh-toan')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_authenticated_profile_and_admin_pages_send_noindex_http_headers(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/profile')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->actingAs($admin)->get('/admin/products')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }
}
