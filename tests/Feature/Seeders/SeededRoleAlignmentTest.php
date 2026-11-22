<?php

namespace Tests\Feature\Seeders;

use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * F1–F5 del spec-user-fillable-restrictivo-fase5 (Fase 5 / 5.11):
 * alineación de la columna `role` del seeder con Spatie.
 *
 * Nota (desviación D1 del spec): el "quirk 1.4" no es reproducible por
 * las vías estándar de seed — `SeedCommand` envuelve la ejecución en
 * `Model::unguarded()` (vendor SeedCommand.php:69), por lo que la clave
 * `'role'` de `firstOrCreate` SÍ se persiste. Estos casos son regresión
 * pura (vigilan la alineación actual), no caracterizaciones a flipear.
 */
class SeededRoleAlignmentTest extends TestCase
{
    use RefreshDatabase;

    private function seedWithKnownDemoPassword(): void
    {
        config(['demo.user_password' => 'DemoPass123']);
        $this->seed(PermissionSeeder::class);
    }

    public function test_f2_seeded_admin_redirects_to_admin_dashboard(): void
    {
        // Arrange
        $this->seedWithKnownDemoPassword();

        // Act
        $response = $this->postJson('/login/store', [
            'email' => 'admin@example.com',
            'password' => 'DemoPass123',
        ]);

        // Assert — columna role alineada con Spatie: redirect admin
        $response->assertOk();
        $this->assertStringContainsString('/admin', (string) $response->json('redirect'));
    }

    public function test_f5_seeded_admin_is_rejected_from_api_app_sign_in(): void
    {
        // Arrange
        $this->seedWithKnownDemoPassword();

        // Act
        $response = $this->postJson('/api/v1/sign-in', [
            'email' => 'admin@example.com',
            'password' => 'DemoPass123',
        ]);

        // Assert — gate por columna: admin no entra al app landlord/tenant
        $response->assertStatus(406);
    }
}
