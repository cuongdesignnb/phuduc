<?php

namespace Tests\Feature\Storefront;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AboutPageContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_returns_canonical_contract_without_raw_settings(): void
    {
        Setting::set('site.name', 'Phu Duc');
        Setting::set('about.title', 'About Phu Duc');
        Setting::set('about.content', '<p>Safe</p><img src="javascript:alert(1)">');
        Setting::set('about.mission', 'Mission');

        $this->get('/gioi-thieu')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Guest/About')
                ->where('page.type', 'about')
                ->where('page.hero.title', 'About Phu Duc')
                ->where('page.about.mission', 'Mission')
                ->missing('settings')
            );
    }
}
