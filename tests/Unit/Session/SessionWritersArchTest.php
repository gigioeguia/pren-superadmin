<?php

namespace Tests\Unit\Session;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Arch-test del spec-session-serialization-json-fase5 (U4): la lista de
 * archivos que escriben sesión está congelada — la sesión corre bajo
 * serialization=json y todo payload nuevo debe pasar conscientemente
 * por la regla PHPStan SessionPayloadRule y este guard de archivos.
 * Un archivo nuevo que escriba sesión (o uno allowlistado que deje de
 * hacerlo) rompe el test y fuerza revisar la allowlist a conciencia.
 */
class SessionWritersArchTest extends TestCase
{
    /**
     * Allowlist congelada: exactamente los 15 escritores actuales
     * (RespondsJson entró en implementación: su forma session()->flash()
     * no estaba en el patrón del inventario del spec — hallazgo del
     * propio guard, documentado como desviación).
     */
    private const WRITERS = [
        'Http/Controllers/Auth/SignupController.php',
        'Http/Controllers/PaymentController.php',
        'Http/Middleware/SetLocale.php',
        'Library/Billplz.php',
        'Library/FedaPayGateway.php',
        'Library/Flutterwave.php',
        'Library/Instamojo.php',
        'Library/Mollie.php',
        'Library/Paystack.php',
        'Library/Razorpay.php',
        'Library/StripeGateway.php',
        'Library/TapPayment.php',
        'Library/Thawani.php',
        'Library/Toyyibpay.php',
        'Traits/RespondsJson.php',
    ];

    public function test_u4_session_writers_match_frozen_allowlist(): void
    {
        $appDir = (string) realpath(__DIR__.'/../../../app');
        $found = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($appDir, FilesystemIterator::SKIP_DOTS),
        );

        $this->assertGreaterThan(0, iterator_count($iterator));

        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }

            $content = (string) file_get_contents($file->getPathname());

            if ($this->writesSession($content)) {
                $found[] = substr($file->getPathname(), strlen($appDir) + 1);
            }
        }

        sort($found);

        $this->assertSame(
            self::WRITERS,
            $found,
            'Session writers drifted (spec 5.21 frozen allowlist) — update the allowlist consciously or move the write.',
        );
    }

    /**
     * Formas de escritura de sesión (mismo alcance que la regla PHPStan
     * SessionPayloadRule: put/push/flash/now + forma array helper).
     */
    private function writesSession(string $content): bool
    {
        $patterns = [
            '/session\(\)\s*->\s*(?:put|push|flash|now)\s*\(/',
            '/Session::(?:put|push|flash|now)\s*\(/',
            '/session\(\s*\[/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content) === 1) {
                return true;
            }
        }

        return false;
    }
}
