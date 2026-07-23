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

    public function test_about_has_non_empty_meta_description_fallback(): void
    {
        Setting::set('site.name', 'Phú Đức');

        $this->get('/gioi-thieu')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.seo.description', 'Giới thiệu về Phú Đức.')
                ->where('page.hero.description', 'Giới thiệu về Phú Đức.')
            );
    }

    public function test_about_organization_omits_empty_contact_and_address(): void
    {
        Setting::set('site.name', 'Phú Đức');

        $this->get('/gioi-thieu')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->missing('page.json_ld.0.contactPoint')
                ->missing('page.json_ld.0.address')
            );
    }

    public function test_about_organization_includes_contact_point_with_phone(): void
    {
        Setting::set('site.name', 'Phú Đức');
        Setting::set('site.phone', '0900000000');

        $this->get('/gioi-thieu')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.json_ld.0.contactPoint.telephone', '0900000000')
                ->missing('page.json_ld.0.address')
            );
    }

    public function test_about_organization_includes_address_when_address_exists(): void
    {
        Setting::set('site.name', 'Phú Đức');
        Setting::set('site.address', 'Hà Nội');

        $this->get('/gioi-thieu')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.json_ld.0.address.streetAddress', 'Hà Nội')
                ->where('page.json_ld.0.address.addressCountry', 'VN')
                ->missing('page.json_ld.0.contactPoint')
            );
    }
}
