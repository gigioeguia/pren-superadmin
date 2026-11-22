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
 * G5 es la caracterización de Seg-29 (IDOR de self-profile admin: el id
 * de la ruta resuelve cualquier cuenta): documenta el comportamiento
 * inseguro vigente y se flipea en el mismo PR que aplica el fix.
 */
class AdminProfileSelfTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    /**
     * @group characterization
     */
    public function test_g5_admin_profile_update_modifies_any_user_id(): void
    {
        // Expected to fail after spec-user-fillable-restrictivo-fase5 (Seg-29 fix).

        // Arrange
        $admin = $this->createAdmin();
        $admin->givePermissionTo(
            Permission::firstOrCreate(['name' => 'profile-update', 'guard_name' => 'web'])
        );
        $victim = User::factory()->landlord()->create([
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

        // Assert — comportamiento inseguro actual (Seg-29): modifica al target
        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $victim->id,
            'name' => 'Hacked Name',
            'email' => 'hacked@example.test',
        ]);
    }
}
