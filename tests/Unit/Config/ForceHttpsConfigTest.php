<?php

namespace Tests\Unit\Config;

use Tests\TestCase;

/**
 * Mapeo env→config de las keys de Seg-23 (Fase 5 / 5.6): FORCE_HTTPS y
 * TRUSTED_PROXIES. Evaluación fresca del archivo real — probar el contrato
 * env→config, no un config() ya resuelto por el container (mecanismo probado
 * por SessionConfigTest, spec 5.5).
 */
class ForceHttpsConfigTest extends TestCase
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

    public function test_force_https_defaults_to_false_when_key_absent(): void
    {
        // Arrange — phpunit.xml no fija FORCE_HTTPS; se limpian las tres
        // fuentes del reader (el leak de putenv tras el test es benigno:
        // las arrays restauradas en tearDown tienen prioridad de lectura).
        unset($_ENV['FORCE_HTTPS'], $_SERVER['FORCE_HTTPS']);
        putenv('FORCE_HTTPS');

        // Act
        $config = $this->loadAppConfig();

        // Assert
        $this->assertSame(false, $config['force_https']);
    }

    public function test_force_https_true_string_is_cast_to_bool(): void
    {
        // Arrange
        $this->withEnv('FORCE_HTTPS', 'true');

        // Act
        $config = $this->loadAppConfig();

        // Assert
        $this->assertSame(true, $config['force_https']);
    }

    public function test_force_https_false_string_is_not_truthy(): void
    {
        // Arrange
        $this->withEnv('FORCE_HTTPS', 'false');

        // Act
        $config = $this->loadAppConfig();

        // Assert
        $this->assertSame(false, $config['force_https']);
    }

    public function test_trusted_proxies_defaults_to_null_when_key_absent(): void
    {
        // Arrange
        unset($_ENV['TRUSTED_PROXIES'], $_SERVER['TRUSTED_PROXIES']);
        putenv('TRUSTED_PROXIES');

        // Act
        $config = $this->loadAppConfig();

        // Assert
        $this->assertNull($config['trusted_proxies']);
    }

    public function test_trusted_proxies_wildcard_passes_through(): void
    {
        // Arrange
        $this->withEnv('TRUSTED_PROXIES', '*');

        // Act
        $config = $this->loadAppConfig();

        // Assert
        $this->assertSame('*', $config['trusted_proxies']);
    }

    public function test_trusted_proxies_csv_list_passes_through_untouched(): void
    {
        // Arrange
        $this->withEnv('TRUSTED_PROXIES', '10.0.0.5,10.0.0.6');

        // Act
        $config = $this->loadAppConfig();

        // Assert
        $this->assertSame('10.0.0.5,10.0.0.6', $config['trusted_proxies']);
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
     * config/app.php es un array puro de env() calls: require directo.
     */
    private function loadAppConfig(): array
    {
        return require dirname(__DIR__, 3).'/config/app.php';
    }
}
