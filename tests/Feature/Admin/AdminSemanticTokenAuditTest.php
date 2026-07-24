<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AdminSemanticTokenAuditTest extends TestCase
{
    public function test_admin_tailwind_palette_exposes_semantic_tokens(): void
    {
        $tailwind = file_get_contents(base_path('tailwind.config.js'));
        $tokens = file_get_contents(base_path('resources/css/admin-tokens.css'));

        foreach (['page', 'surface', 'surface-muted', 'border', 'content', 'content-muted', 'accent', 'accent-hover', 'danger', 'warning', 'success', 'focus'] as $token) {
            $this->assertStringContainsString($token, $tailwind);
            $this->assertStringContainsString("--admin-{$token}", $tokens);
        }
    }
}
