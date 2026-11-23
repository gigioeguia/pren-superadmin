<?php

namespace Tests\Unit\Config;

use Tests\TestCase;

/**
 * U1 del spec-session-serialization-json-fase5: el flip es literal
 * (sync con el skeleton L13) — `'serialization' => 'json'` sin válvula
 * env (Q4), así que evaluar el archivo fresco basta, sin mutar $_ENV.
 * Tests\TestCase (no PHPUnit puro) porque config/session.php llama
 * storage_path() — mismo motivo que SessionConfigTest (5.5).
 */
class SessionSerializationConfigTest extends TestCase
{
    public function test_u1_session_serialization_defaults_to_json_without_env_valve(): void
    {
        // Arrange
        $path = base_path('config/session.php');

        // Act
        $config = require $path;

        // Assert — literal json, sin env() nueva (Q4: sin válvula de reversión).
        $this->assertIsArray($config);
        $this->assertSame('json', $config['serialization'] ?? null);
        $this->assertStringNotContainsString(
            'SESSION_SERIALIZATION',
            (string) file_get_contents($path),
        );
    }
}
