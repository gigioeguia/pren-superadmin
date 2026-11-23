<?php

namespace Tests\Feature\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

/**
 * F1–F4 del spec 5.9 (Seg-26): `ops:verify-deploy` verifica la postura
 * efectiva (patrón de evidencia 5.9). La suite fuerza config en runtime
 * (patrón ForceHttpsSmokeTest, 5.6) y nunca muta `$_ENV`; el comando se
 * invoca con Artisan::call + BufferedOutput para asertar exit code y
 * salida.
 */
#[Group('smoke')]
#[Group('security')]
class VerifyDeployTest extends TestCase
{
    /**
     * Postura de producción completa (F1): todos los HARD en verde.
     */
    private const PRODUCTION_POSTURE = [
        'app.env' => 'production',
        'app.debug' => false,
        'logging.channels.single.level' => 'warning',
        'session.driver' => 'database',
        'app.url' => 'https://prod.example.com',
        'app.force_https' => true,
    ];

    /**
     * Desviaciones de F2: una por corrida, una fila FAIL por check HARD.
     *
     * @var array<string, array<string, mixed>>
     */
    private const HARD_DEVIATIONS = [
        'app.debug' => ['app.debug' => true],
        'log level' => ['logging.channels.single.level' => 'debug'],
        'session.driver' => ['session.driver' => 'file'],
        'app.url' => ['app.url' => 'http://prod.example.com'],
        'app.force_https' => ['app.force_https' => false],
        'session.serialization' => ['session.serialization' => 'php'],
    ];

    /**
     * @return array{0: int, 1: string}
     */
    private function runCommand(): array
    {
        $code = Artisan::call('ops:verify-deploy', [], $buffered = new BufferedOutput);

        return [$code, $buffered->fetch()];
    }

    public function test_f1_production_posture_passes(): void
    {
        // Arrange
        config(self::PRODUCTION_POSTURE);

        // Act
        [$code, $out] = $this->runCommand();

        // Assert
        $this->assertSame(Command::SUCCESS, $code);
        $this->assertStringContainsString('APP_ENV: production — enforcing', $out);
        $this->assertStringContainsString('PASS', $out);
        $this->assertStringNotContainsString('FAIL', $out);
    }

    public function test_f2_each_broken_hard_check_fails(): void
    {
        foreach (self::HARD_DEVIATIONS as $check => $override) {
            // Arrange
            config(self::PRODUCTION_POSTURE);
            config($override);

            // Act
            [$code, $out] = $this->runCommand();

            // Assert
            $this->assertSame(Command::FAILURE, $code, "[{$check}] must fail under enforcement");
            $this->assertStringContainsString('FAIL', $out, "[{$check}] row must be visible");
            $this->assertStringContainsString($check, $out, "[{$check}] label must appear in the table");
        }
    }

    public function test_f3_non_production_env_is_report_only(): void
    {
        // Arrange — dev desvía legítimamente (Q3a/Q11a): debug local.
        config([
            'app.env' => 'local',
            'app.debug' => true,
            'logging.channels.single.level' => 'debug',
        ]);

        // Act
        [$code, $out] = $this->runCommand();

        // Assert
        $this->assertSame(Command::SUCCESS, $code);
        $this->assertStringContainsString('report-only', $out);
        $this->assertStringContainsString('FAIL', $out);
    }

    public function test_f4_output_never_contains_app_key(): void
    {
        // Arrange
        config(self::PRODUCTION_POSTURE);

        // Act
        [$code, $out] = $this->runCommand();

        // Assert — la fila existe (set/missing), el valor jamás.
        $this->assertSame(Command::SUCCESS, $code);
        $this->assertStringContainsString('app.key', $out);
        $this->assertStringNotContainsString((string) config('app.key'), $out);
    }

    /**
     * F5 del spec 5.20 (PHP 8.4): el 6º HARD `php version` es visible con
     * expected/effective correctos y pasa — PHP_VERSION es runtime, en
     * todo entorno donde esta suite puede correr es >= 8.4.1 (floor D1).
     */
    public function test_f5_php_version_row_visible_and_passes(): void
    {
        // Arrange
        config(self::PRODUCTION_POSTURE);

        // Act
        [$code, $out] = $this->runCommand();

        // Assert
        $this->assertSame(Command::SUCCESS, $code);
        $this->assertStringContainsString('php version', $out);
        $this->assertStringContainsString('>= 8.4.1', $out);
        $this->assertStringContainsString(PHP_VERSION, $out);
        $this->assertStringNotContainsString('FAIL', $out);
    }

    /**
     * F6 del spec 5.21 (session serialization=json): el 7º HARD es
     * visible y pasa con el default del config (la rama FAIL la cubre la
     * matriz de F2 vía `session.serialization` => php).
     */
    public function test_f6_session_serialization_row_visible_and_passes(): void
    {
        // Arrange
        config(self::PRODUCTION_POSTURE);

        // Act
        [$code, $out] = $this->runCommand();

        // Assert
        $this->assertSame(Command::SUCCESS, $code);
        $this->assertStringContainsString('session.serialization', $out);
        $this->assertStringContainsString('json', $out);
        $this->assertStringNotContainsString('FAIL', $out);
    }
}
