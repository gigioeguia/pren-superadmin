<?php

namespace Tests\Unit\Seeders;

use PHPUnit\Framework\TestCase;

/**
 * Arch-tests del spec 4.10: los seeders no deben reintroducir IDs/rutas
 * hardcodeadas ni credenciales embebidas.
 */
class SeedersArchTest extends TestCase
{
    /**
     * @return array<int, string>
     */
    private function seederFiles(): array
    {
        $files = array_merge(
            glob(__DIR__.'/../../../database/seeders/*.php') ?: [],
            glob(__DIR__.'/../../../database/seeders/Concerns/*.php') ?: [],
        );

        $this->assertNotEmpty($files);

        return $files;
    }

    public function test_u1_no_literal_fk_ids_in_seeders(): void
    {
        $pattern = '/[\'"](landlord_id|tenant_id|user_id|currency_id|category_id|house_type_id|property_id|plan_id|gateway_id|income_category_id|expense_category_id|rent_id|unit_id)[\'"]\s*=>\s*[\'"]?\d+/';

        foreach ($this->seederFiles() as $file) {
            $content = (string) file_get_contents($file);

            $this->assertDoesNotMatchRegularExpression($pattern, $content, "Literal FK id found in {$file}");
        }
    }

    public function test_u2_no_env_calls_in_seeders(): void
    {
        foreach ($this->seederFiles() as $file) {
            $content = (string) file_get_contents($file);

            $this->assertStringNotContainsString('env(', $content, "env() call in {$file} — use config() (Fase 4.3)");
        }
    }

    public function test_u3_no_embedded_credentials_in_database_code(): void
    {
        $this->assertFileDoesNotExist(__DIR__.'/../../../database/seeders/FedaPayGatewaySeeder.php');

        $files = array_merge(
            $this->seederFiles(),
            glob(__DIR__.'/../../../database/factories/*.php') ?: [],
        );

        $pattern = '/sk_(sandbox|live)_[A-Za-z0-9]+/';

        foreach ($files as $file) {
            $content = (string) file_get_contents($file);

            $this->assertDoesNotMatchRegularExpression($pattern, $content, "Embedded gateway credential in {$file}");
        }
    }
}
