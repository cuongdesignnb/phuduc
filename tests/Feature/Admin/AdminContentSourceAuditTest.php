<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AdminContentSourceAuditTest extends TestCase
{
    public function test_pr3b_audit_script_and_package_command_exist(): void
    {
        $this->assertFileExists(base_path('scripts/audit-pr3b-admin-content.mjs'));
        $package = json_decode(file_get_contents(base_path('package.json')), true);
        $this->assertSame('node scripts/audit-pr3b-admin-content.mjs', $package['scripts']['audit:pr3b-admin-content']);
    }
}
