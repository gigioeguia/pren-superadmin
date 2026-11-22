<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * G1–G3 del spec-user-fillable-restrictivo-fase5 (Fase 5 / 5.11).
 *
 * G1 y G2 son caracterizaciones de Seg-28 (inyección masiva en los
 * endpoints admin de users): documentan el comportamiento inseguro
 * vigente y se flipean en el mismo PR que aplica el fix.
 */
class UserAdminMassAssignmentTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    private function adminWithPermission(string $permission): User
    {
        $admin = $this->createAdmin();
        $admin->givePermissionTo(
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web'])
        );

        return $admin;
    }

    /**
     * @group characterization
     */
    public function test_g1_admin_user_store_injection_is_persisted(): void
    {
        // Expected to fail after spec-user-fillable-restrictivo-fase5 (Seg-28 fix).

        // Arrange
        $admin = $this->adminWithPermission('users-create');
        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->postJson('/admin/users', [
                'name' => 'Staff User',
                'email' => 'staff@example.test',
                'role' => 'staff',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
                'balance' => 888,
                'will_expire' => '2099-12-31 00:00:00',
            ]);

        // Assert — comportamiento inseguro actual
        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'staff@example.test',
            'balance' => 888,
            'will_expire' => '2099-12-31 00:00:00',
        ]);
    }

    /**
     * @group characterization
     */
    public function test_g2_admin_user_update_injection_is_persisted(): void
    {
        // Expected to fail after spec-user-fillable-restrictivo-fase5 (Seg-28 fix).

        // Arrange
        $admin = $this->adminWithPermission('users-update');
        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $victim = User::factory()->landlord()->create([
            'email' => 'victim@example.test',
            'balance' => 0,
        ]);

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->putJson("/admin/users/{$victim->id}", [
                'name' => 'Staff User',
                'email' => 'victim@example.test',
                'role' => 'staff',
                'balance' => 777,
                'will_expire' => '2099-12-31 00:00:00',
            ]);

        // Assert — comportamiento inseguro actual
        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $victim->id,
            'balance' => 777,
            'will_expire' => '2099-12-31 00:00:00',
        ]);
    }
}
