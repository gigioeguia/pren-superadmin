<?php

namespace Tests\Unit\Hygiene;

use PHPUnit\Framework\TestCase;

/**
 * U19 del spec-public-writes-throttle-fase5 (Fase 5 / 5.14):
 * los controladores de writes públicos no reintroducen
 * `$request->except(...)` ni `$request->all(...)` (migrados a arrays explícitos).
 */
class PublicWritesArchTest extends TestCase
{
    /**
     * @var array<int, string>
     */
    private const PUBLIC_WRITING_FILES = [
        'app/Http/Controllers/BlogController.php',
        'app/Http/Controllers/ContactController.php',
    ];

    public function test_u19_no_request_except_or_all_in_public_writers(): void
    {
        foreach (self::PUBLIC_WRITING_FILES as $relative) {
            $path = __DIR__.'/../../../'.$relative;

            $this->assertFileExists($path);

            $content = (string) file_get_contents($path);

            $this->assertStringNotContainsString(
                '$request->except(',
                $content,
                "Patrón except() reintroducido en {$relative} (spec 5.14)"
            );

            $this->assertStringNotContainsString(
                '$request->all(',
                $content,
                "Patrón all() reintroducido en {$relative} (spec 5.14)"
            );
        }
    }
}
