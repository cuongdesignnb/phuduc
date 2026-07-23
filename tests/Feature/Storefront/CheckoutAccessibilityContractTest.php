<?php

namespace Tests\Feature\Storefront;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutAccessibilityContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_has_error_summary_focus_and_field_descriptions(): void
    {
        $source = file_get_contents(resource_path('js/Pages/Guest/Checkout.vue'));

        foreach (['FormField', 'role="alert"', 'aria-live="polite"', 'aria-describedby', 'aria-invalid', 'formElement', 'novalidate', 'firstErrorKey'] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
    }

    public function test_lookup_pages_announce_results_and_failures(): void
    {
        foreach (['OrderLookup', 'WarrantyLookup'] as $page) {
            $source = file_get_contents(resource_path("js/Pages/Guest/{$page}.vue"));

            $this->assertStringContainsString('role="status"', $source);
            $this->assertStringContainsString('role="alert"', $source);
            $this->assertStringContainsString('aria-describedby', $source);
            $this->assertStringContainsString('resultRegion', $source);
        }
    }

    public function test_cart_clear_uses_an_accessible_confirmation_dialog(): void
    {
        $source = file_get_contents(resource_path('js/Pages/Guest/Cart.vue'));

        foreach (['role="dialog"', 'aria-modal="true"', 'aria-haspopup="dialog"', 'confirmClear', 'cancelClear'] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
    }
}
