<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * G1–G3 del spec-user-fillable-restrictivo-fase5 (Fase 5 / 5.11).
 *
 * G1/G2 cerraban Seg-28 (inyección masiva en los endpoints admin de
 * users): nacieron como caracterización y se flipearon con el fix del
 * mismo PR. G3 es regresión del flujo legítimo.
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

    public function test_g1_admin_user_store_ignores_injected_columns(): void
    {
        // Arrange
        $admin = $this->adminWithPermission('users-create');
        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson('/admin/users', [
                'name' => 'Staff User',
                'email' => 'staff@example.test',
                'role' => 'staff',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
                'balance' => 888,
                'will_expire' => '2099-12-31',
            ]);

        // Assert — Seg-28 cerrado: solo name/email/phone + image/password
        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'staff@example.test',
            'name' => 'Staff User',
            'balance' => 0,
            'will_expire' => null,
        ]);
    }

    public function test_g2_admin_user_update_ignores_injected_columns(): void
    {
        // Arrange
        $admin = $this->adminWithPermission('users-update');
        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $victim = User::factory()->landlord()->create([
            'email' => 'victim@example.test',
            'balance' => 0,
        ]);
        $victim->refresh();
        $originalWillExpire = $victim->will_expire;

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->putJson("/admin/users/{$victim->id}", [
                'name' => 'Staff User',
                'email' => 'victim@example.test',
                'role' => 'staff',
                'balance' => 777,
                'will_expire' => '2099-12-31',
            ]);

        // Assert — Seg-28 cerrado: las claves inyectadas se ignoran
        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $victim->id,
            'name' => 'Staff User',
            'balance' => 0,
        ]);
        $this->assertSame($originalWillExpire, $victim->fresh()->will_expire);
    }

    public function test_g3_admin_user_store_keeps_role_assignment(): void
    {
        // Arrange
        $admin = $this->adminWithPermission('users-create');
        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson('/admin/users', [
                'name' => 'Staff User',
                'email' => 'staff-role@example.test',
                'role' => 'staff',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
            ]);

        // Assert — asignación por propiedad + Spatie sync intactos
        $response->assertOk();

        $user = User::where('email', 'staff-role@example.test')->first();
        $this->assertSame('staff', $user->role);
        $this->assertTrue($user->hasRole('staff'));
    }
}
