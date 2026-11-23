<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Contract tests F14–F15 (spec-tx-legadas-services-fase5, Fase 5 / 5.18).
 *
 * Congelan el contrato observable de Admin/RoleController antes de
 * mover la tx a RoleService. F15 no cambia tras D-FIX-role-update:
 * el fix envuelve las dos escrituras en una tx (solo cambia la
 * semántica de crash, no el happy path).
 */
class RoleTxContractTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    /**
     * @return array<int, Permission>
     */
    private function twoPermissions(): array
    {
        return [
            Permission::create(['name' => 'demo-read']),
            Permission::create(['name' => 'demo-update']),
        ];
    }

    public function test_f14_admin_role_store_creates_role_with_permissions(): void
    {
        // Arrange
        $superadmin = $this->createSuperadmin();
        [$read, $update] = $this->twoPermissions();

        // Act
        $response = $this->actingAs($superadmin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson('/admin/roles', [
                'name' => 'Editor',
                'permissions' => [$read->id, $update->id],
            ]);

        // Assert
        $response->assertOk()
            ->assertJsonPath('message', __('Role created successfully'))
            ->assertJsonPath('redirect', route('admin.roles.index'));

        $role = Role::where('name', 'Editor')->first();
        $this->assertNotNull($role);
        $this->assertSame(
            [$read->id, $update->id],
            $role->permissions()->orderBy('id')->pluck('id')->all()
        );
    }

    public function test_f15_admin_role_update_resyncs_permissions(): void
    {
        // Arrange
        $superadmin = $this->createSuperadmin();
        [$read, $update] = $this->twoPermissions();
        $delete = Permission::create(['name' => 'demo-delete']);

        $role = Role::create(['name' => 'Editor']);
        $role->permissions()->sync([$read->id]);

        // Act
        $response = $this->actingAs($superadmin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->putJson("/admin/roles/{$role->id}", [
                'name' => 'Senior Editor',
                'permissions' => [$update->id, $delete->id],
            ]);

        // Assert — [D-FIX-role-update]: role + sync ahora atómicos
        $response->assertOk()
            ->assertJsonPath('message', __('Role update successfully'))
            ->assertJsonPath('redirect', route('admin.roles.index'));

        $fresh = $role->fresh();
        $this->assertSame('Senior Editor', $fresh->name);
        $this->assertSame(
            [$update->id, $delete->id],
            $fresh->permissions()->orderBy('id')->pluck('id')->all()
        );
    }
}
