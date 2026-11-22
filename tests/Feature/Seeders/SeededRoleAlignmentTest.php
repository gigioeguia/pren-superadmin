<?php

namespace Tests\Feature\Seeders;

use App\Models\User;
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

    public function test_f1_seeded_role_column_matches_spatie(): void
    {
        // Arrange
        $this->seedWithKnownDemoPassword();

        // Assert — asignación explícita (Q6): columna y Spatie alineadas
        $admin = User::where('email', 'admin@example.com')->first();
        $this->assertSame('admin', $admin->role);
        $this->assertTrue($admin->hasRole('admin'));

        $superadmin = User::where('email', 'superadmin@example.com')->first();
        $this->assertSame('superadmin', $superadmin->role);
        $this->assertTrue($superadmin->hasRole('superadmin'));
    }

    public function test_f3_notify_users_reaches_seeded_superadmin(): void
    {
        // Arrange
        $this->seedWithKnownDemoPassword();

        // Act
        $recipients = notify_users();

        // Assert — el superadmin demo ya no queda fuera de las notificaciones
        $this->assertTrue($recipients->contains('email', 'superadmin@example.com'));
        $this->assertTrue($recipients->contains('email', 'admin@example.com'));
    }

    public function test_f4_support_receiver_resolves_seeded_superadmin(): void
    {
        // Arrange
        $this->seedWithKnownDemoPassword();

        // Act — la query exacta que consume SupportService:18
        $receiver = User::where('role', 'superadmin')->first();

        // Assert
        $this->assertNotNull($receiver);
        $this->assertSame('superadmin@example.com', $receiver->email);
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
