<?php

namespace Tests\Unit\Controllers;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Arch-test del paraguas spec-unificar-capas-services-fase5 (§5.2),
 * activado por el spec-controladores-residuales-fase5 (U1–U2). Estilo
 * SeedersArchTest: lee archivos, sin DB.
 *
 * U2 — allowlist D1 (decisor 2026-11-22): la premisa del paraguas
 * ("los 8 controladores con tx están todos en scope") era inexacta.
 * Las 9 excepciones viven en entidades OUT del paraguas (§2: withdraw,
 * profile, role, api user/security-deposit) o fueron conservadas a
 * propósito (Api/Auth/RegistrationController, hijo 4 §11). Cualquier
 * tx NUEVA en un controlador rompe este test; la migración de las 9
 * exige specs propios (rastreadas en .analisis/plan/plan-accion.md).
 */
class ControllersLineLimitArchTest extends TestCase
{
    /**
     * Controladores con transacciones legadas documentadas.
     *
     * @var array<int, string>
     */
    private const TX_ALLOWLIST = [
        'Api/Auth/RegistrationController.php',   // hijo 4 §11: unidad atómica User+trío, diseño intencional
        'Api/SecurityDepositController.php',     // deuda lateral (offline deposit) — plan-accion
        'Admin/RoleController.php',              // deuda lateral — plan-accion
    ];

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
        $allowlist = array_map(
            fn (string $relative) => str_replace('/', DIRECTORY_SEPARATOR, __DIR__.'/../../../app/Http/Controllers/'.$relative),
            self::TX_ALLOWLIST
        );

        foreach ($this->controllerFiles() as $file) {
            if (in_array($file, $allowlist, true)) {
                continue;
            }

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

        // Guard del allowlist: cada entrada debe existir y seguir
        // declarando una tx (retirarla cuando el service la absorba).
        foreach ($allowlist as $file) {
            $this->assertFileExists($file, 'Entrada del TX_ALLOWLIST inexistente — limpiarla.');

            $content = (string) file_get_contents($file);
            $hasTransaction = str_contains($content, 'DB::beginTransaction') || str_contains($content, 'DB::transaction(');

            $this->assertTrue(
                $hasTransaction,
                sprintf('%s ya no declara tx — retirarla del TX_ALLOWLIST.', basename($file))
            );
        }
    }
}
