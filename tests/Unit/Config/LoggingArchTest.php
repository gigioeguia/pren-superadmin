<?php

namespace Tests\Unit\Config;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Arch-test del spec 5.9 (Seg-26): los defaults seguros de logging no
 * deben reintroducirse — fallback `warning` en config y guía `warning`
 * en .env.example. String-based sobre archivos, sin boot (patrón
 * SeedersArchTest).
 */
class LoggingArchTest extends TestCase
{
    #[Group('smoke')]
    public function test_u1_env_example_pins_safe_log_defaults(): void
    {
        // Arrange
        $content = (string) file_get_contents(__DIR__.'/../../../.env.example');

        // Act & Assert
        $this->assertStringContainsString('LOG_LEVEL=warning', $content);
        $this->assertStringContainsString('LOG_DEPRECATIONS_CHANNEL=null', $content);
    }

    #[Group('smoke')]
    public function test_u2_config_never_reintroduces_debug_fallback(): void
    {
        // Arrange
        $content = (string) file_get_contents(__DIR__.'/../../../config/logging.php');

        // Act & Assert — slack no colisiona: su fallback es 'critical'.
        $this->assertStringNotContainsString("env('LOG_LEVEL', 'debug')", $content);
        $this->assertStringContainsString("env('LOG_LEVEL', 'warning')", $content);
    }
}
