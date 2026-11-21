<?php

namespace Tests\Unit\Config;

use Tests\TestCase;

/**
 * config/session.php llama storage_path() (l.62), por lo que la evaluación
 * fresca del archivo requiere app booteada: Tests\TestCase, no el TestCase
 * puro de PHPUnit que usa SeedersArchTest.
 */
class SessionConfigTest extends TestCase
{
    private array $envBackup = [];

    private array $serverBackup = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Snapshot tras el boot: dotenv puede añadir keys de .env (nunca
        // sobreescribe las de phpunit.xml — writer inmutable).
        $this->envBackup = $_ENV;
        $this->serverBackup = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_ENV = $this->envBackup;
        $_SERVER = $this->serverBackup;

        parent::tearDown();
    }

    public function test_app_url_https_marks_cookie_secure(): void
    {
        // Arrange
        unset($_ENV['SESSION_SECURE_COOKIE'], $_SERVER['SESSION_SECURE_COOKIE']);
        $this->withEnv('APP_URL', 'https://app.example.com');

        // Act
        $config = $this->loadSessionConfig();

        // Assert
        $this->assertSame(true, $config['secure']);
    }

    public function test_app_url_http_does_not_mark_cookie_secure(): void
    {
        // Arrange
        unset($_ENV['SESSION_SECURE_COOKIE'], $_SERVER['SESSION_SECURE_COOKIE']);
        $this->withEnv('APP_URL', 'http://localhost');

        // Act
        $config = $this->loadSessionConfig();

        // Assert
        $this->assertSame(false, $config['secure']);
    }

    public function test_secure_cookie_override_true_wins_over_http_app_url(): void
    {
        // Arrange
        $this->withEnv('APP_URL', 'http://localhost');
        $this->withEnv('SESSION_SECURE_COOKIE', 'true');

        // Act
        $config = $this->loadSessionConfig();

        // Assert
        $this->assertSame(true, $config['secure']);
    }

    public function test_secure_cookie_override_false_wins_over_https_app_url(): void
    {
        // Arrange
        $this->withEnv('APP_URL', 'https://app.example.com');
        $this->withEnv('SESSION_SECURE_COOKIE', 'false');

        // Act
        $config = $this->loadSessionConfig();

        // Assert
        $this->assertSame(false, $config['secure']);
    }

    public function test_driver_defaults_to_database_when_env_key_absent(): void
    {
        // Arrange
        // El reader de env() consulta $_ENV → $_SERVER → getenv(): hay que
        // limpiar las tres fuentes (phpunit fija SESSION_DRIVER=array en
        // las tres). El leak de putenv tras el test es benigno: las arrays
        // restauradas en tearDown tienen prioridad de lectura.
        unset($_ENV['SESSION_DRIVER'], $_SERVER['SESSION_DRIVER']);
        putenv('SESSION_DRIVER');

        // Act
        $config = $this->loadSessionConfig();

        // Assert
        $this->assertSame('database', $config['driver']);
    }

    /**
     * El reader de dotenv consulta $_ENV/$_SERVER en cada env();
     * la inmutabilidad del writer no afecta mutaciones directas.
     */
    private function withEnv(string $key, string $value): void
    {
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    /**
     * Evaluación fresca del archivo real de config: probar el mapeo
     * env→config, no un config() ya resuelto por el container.
     */
    private function loadSessionConfig(): array
    {
        return require dirname(__DIR__, 3).'/config/session.php';
    }
}
