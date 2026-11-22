<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * G4–G5 del spec-user-fillable-restrictivo-fase5 (Fase 5 / 5.11).
 *
 * G5 cerraba Seg-29 (IDOR de self-profile admin: el id de la ruta
 * resolvía cualquier cuenta): nació como caracterización y se flipeó con
 * el fix del mismo PR. G4 es regresión del flujo legítimo.
 */
class AdminProfileSelfTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    private function adminWithProfileUpdate(): User
    {
        $admin = $this->createAdmin();
        $admin->givePermissionTo(
            Permission::firstOrCreate(['name' => 'profile-update', 'guard_name' => 'web'])
        );

        return $admin;
    }

    public function test_g4_admin_updates_own_profile(): void
    {
        // Arrange
        $admin = $this->adminWithProfileUpdate();

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->putJson("/admin/profile/{$admin->id}", [
                'name' => 'Updated Admin',
                'email' => 'updated-admin@example.test',
                'phone' => ['mobile_code' => '+1', 'mobile_no' => '555'],
            ]);

        // Assert
        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Updated Admin',
            'email' => 'updated-admin@example.test',
        ]);
    }

    public function test_g5_admin_profile_update_ignores_foreign_id(): void
    {
        // Arrange
        $admin = $this->adminWithProfileUpdate();
        $victim = $this->createLandlord([
            'name' => 'Original Name',
            'email' => 'victim@example.test',
        ]);

        // Act — el id de la ruta apunta a otra cuenta
        $response = $this->actingAs($admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->putJson("/admin/profile/{$victim->id}", [
                'name' => 'Hacked Name',
                'email' => 'hacked@example.test',
                'phone' => ['mobile_code' => '+1', 'mobile_no' => '555'],
            ]);

        // Assert — Seg-29 cerrado: el target es siempre el usuario de sesión
        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $victim->id,
            'name' => 'Original Name',
            'email' => 'victim@example.test',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Hacked Name',
        ]);
    }
}
