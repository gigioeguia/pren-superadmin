<?php

namespace Tests\Unit\Hygiene;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Arch-tests del spec 5.10 (DT-B1): el prefijo vendor no debe
 * reintroducirse en código ni en nombres de archivo.
 */
class VendorPrefixArchTest extends TestCase
{
    private const TEXT_EXTENSIONS = ['php', 'js', 'css', 'md', 'json'];

    /**
     * @param  array<int, string>  $dirs
     * @return array<int, string>
     */
    private function textFilesIn(array $dirs): array
    {
        $files = [];

        foreach ($dirs as $dir) {
            $path = __DIR__.'/../../../'.$dir;
            $this->assertDirectoryExists($path);

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (in_array(strtolower($file->getExtension()), self::TEXT_EXTENSIONS, true)) {
                    $files[] = $file->getPathname();
                }
            }
        }

        $this->assertNotEmpty($files);

        return $files;
    }

    /**
     * @param  array<int, string>  $dirs
     */
    private function assertNoVendorPrefix(array $dirs): void
    {
        $needle = 'ac'.'noo'; // dinámico: el propio guard no contiene el literal

        foreach ($this->textFilesIn($dirs) as $file) {
            if ($file === __FILE__) {
                continue; // self-exclusión (U2 escanea tests/)
            }

            $content = strtolower((string) file_get_contents($file));

            $this->assertStringNotContainsString($needle, $content, "Vendor prefix in {$file}");
            $this->assertStringNotContainsString($needle, strtolower(basename($file)), "Vendor prefix in filename {$file}");
        }
    }

    public function test_u1_no_vendor_prefix_in_app_and_routes(): void
    {
        $this->assertNoVendorPrefix(['app', 'routes']);
    }

    public function test_u2_no_vendor_prefix_global(): void
    {
        $this->assertNoVendorPrefix(['app', 'routes', 'config', 'database', 'tests', 'resources', 'lang', 'public']);
    }
}
