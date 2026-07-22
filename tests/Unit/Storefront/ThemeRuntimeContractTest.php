<?php

namespace Tests\Unit\Storefront;

use App\Services\Storefront\ThemeTokenService;
use PHPUnit\Framework\TestCase;

class ThemeRuntimeContractTest extends TestCase
{
    private static function runtimePath(): string
    {
        return dirname(__DIR__, 3)
            .DIRECTORY_SEPARATOR.'resources'
            .DIRECTORY_SEPARATOR.'js'
            .DIRECTORY_SEPARATOR.'Composables'
            .DIRECTORY_SEPARATOR.'useThemeRuntime.js';
    }

    public function test_runtime_allowlist_contains_every_backend_theme_token(): void
    {
        $runtime = file_get_contents(self::runtimePath());
        $this->assertNotFalse($runtime, 'Could not read useThemeRuntime.js');

        foreach ((new ThemeTokenService)->cssVariableNames() as $variable) {
            $this->assertStringContainsString(
                "'{$variable}'",
                $runtime,
                "Runtime allowlist is missing {$variable}.",
            );
        }
    }

    public function test_runtime_allowlist_does_not_contain_user_controlled_tokens(): void
    {
        $runtime = file_get_contents(self::runtimePath());
        $this->assertNotFalse($runtime, 'Could not read useThemeRuntime.js');

        $this->assertStringNotContainsString(
            "'--user-controlled'",
            $runtime,
            'Runtime allowlist must not accept arbitrary user-controlled tokens.',
        );
    }
}
