<?php

namespace Tests\Unit\Controllers;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Arch-test del paraguas spec-unificar-capas-services-fase5 (§5.2),
 * activado por el spec-controladores-residuales-fase5 (U1–U2) y
 * cerrado por el spec-tx-legadas-services-fase5 (U1 absoluto,
 * 2026-11-22). Estilo SeedersArchTest: lee archivos, sin DB.
 *
 * U2 — regla absoluta desde spec-tx-legadas-services-fase5: las 9 tx
 * legadas del allowlist D1 (8 deuda lateral + Registration, cuyo
 * supersede del hijo 4 §11 movió la tx a LandlordService) viven hoy en
 * services. Ningún controlador puede declarar una transacción: la tx
 * va en el service (Fase 4/4.8) — sin excepciones.
 */
class ControllersLineLimitArchTest extends TestCase
{
    /**
     * @return array<int, string>
     */
    private function controllerFiles(): array
    {
        $directory = __DIR__.'/../../../app/Http/Controllers';
        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        $this->assertNotEmpty($files, 'No se encontraron controladores — ¿se movió app/Http/Controllers?');

        return $files;
    }

    public function test_u1_controllers_stay_under_200_lines(): void
    {
        foreach ($this->controllerFiles() as $file) {
            $content = trim((string) file_get_contents($file));
            $lineCount = substr_count($content, "\n") + 1;

            $this->assertLessThan(
                200,
                $lineCount,
                sprintf('%s tiene %d líneas — mover la lógica a un service (paraguas 5.13).', basename($file), $lineCount)
            );
        }
    }

    public function test_u2_no_business_transactions_in_controllers(): void
    {
        foreach ($this->controllerFiles() as $file) {
            $content = (string) file_get_contents($file);

            $this->assertStringNotContainsString(
                'DB::beginTransaction',
                $content,
                sprintf('%s abre una transacción en el controlador — la tx va en el service (Fase 4/4.8).', basename($file))
            );

            $this->assertStringNotContainsString(
                'DB::transaction(',
                $content,
                sprintf('%s abre una transacción en el controlador — la tx va en el service (Fase 4/4.8).', basename($file))
            );
        }
    }
}
