<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AdminModalAccessibilityContractTest extends TestCase
{
    public function test_modal_and_drawer_share_focus_lifecycle_contract(): void
    {
        $focus = file_get_contents(base_path('resources/js/Composables/useModalFocus.js'));
        $confirm = file_get_contents(base_path('resources/js/Components/Admin/AdminConfirmDialog.vue'));
        $drawer = file_get_contents(base_path('resources/js/Components/Admin/AdminMobileNavigation.vue'));

        $this->assertStringContainsString('document.activeElement', $focus);
        $this->assertStringContainsString('document.body.style.overflow', $focus);
        $this->assertStringContainsString("event.key !== 'Tab'", $focus);
        $this->assertStringContainsString("event.key === 'Escape'", $focus);
        $this->assertStringContainsString('useModalFocus', $confirm);
        $this->assertStringContainsString('aria-labelledby', $confirm);
        $this->assertStringContainsString('aria-describedby', $confirm);
        $this->assertStringContainsString('processing', $confirm);
        $this->assertStringContainsString('useModalFocus', $drawer);
        $this->assertStringContainsString('role="dialog"', $drawer);
    }
}
