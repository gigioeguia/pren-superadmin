<?php

namespace Tests\Unit\Views;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Arch-tests del spec-get-option-tipos-consistentes-fase5 (Fase 5 / 5.7,
 * Seg-24): no reintroducir accesos directos a get_option()[...] sin guard
 * ni referencias a la key fantasma logo_setting.
 *
 * Limitacion conocida (spec §6.2): el scan es por linea — accesos
 * multilínea quedan fuera del regex; los 38 conocidos son single-line.
 */
class UnguardedOptionAccessTest extends TestCase
{
    /**
     * @return array<int, string>
     */
    private function phpFilesUnder(string $base): array
    {
        $this->assertDirectoryExists($base);

        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        $this->assertNotEmpty($files);

        return $files;
    }

    public function test_u10_no_unguarded_get_option_index_access(): void
    {
        // Arrange
        $violations = [];

        // Act
        foreach ($this->phpFilesUnder(__DIR__.'/../../../resources/views') as $file) {
            $lines = file($file) ?: [];

            foreach ($lines as $number => $line) {
                $hasAccess = (bool) preg_match('/get_option\s*\([^()]*\)\s*\[/', $line);

                if ($hasAccess && ! str_contains($line, '??')) {
                    $violations[] = ($number + 1).': '.trim($line).' ('.$file.')';
                }
            }
        }

        // Assert
        $this->assertSame([], $violations, "Unguarded get_option()[...] access:\n".implode("\n", $violations));
    }

    public function test_u11_no_logo_setting_references(): void
    {
        // Arrange
        $files = array_merge(
            $this->phpFilesUnder(__DIR__.'/../../../app'),
            $this->phpFilesUnder(__DIR__.'/../../../resources'),
        );

        // Act + Assert
        foreach ($files as $file) {
            $content = (string) file_get_contents($file);

            $this->assertStringNotContainsString(
                'logo_setting',
                $content,
                "logo_setting reference found in {$file} — use get_option('general')['logo'] (spec 5.7)"
            );
        }
    }
}
