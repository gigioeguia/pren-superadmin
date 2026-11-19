<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class AdminGuardSmokeTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // P4 del spec: roles/permisos Spatie en la DB de testing
        $this->seed(PermissionSeeder::class);
    }

    public function test_anonymous_user_is_redirected_to_login(): void
    {
        // Act
        $response = $this->get('/admin');

        // Assert
        $response->assertRedirect(route('login'));
    }

    public function test_landlord_is_redirected_to_login(): void
    {
        // Arrange
        $landlord = $this->createLandlord();

        // Act
        $response = $this->actingAs($landlord)->get('/admin');

        // Assert
        $response->assertRedirect(route('login'));
    }

    public function test_admin_with_permission_renders_dashboard(): void
    {
        // Arrange
        $admin = $this->createAdmin();

        // Act
        $response = $this->actingAs($admin)->get('/admin');

        // Assert
        $response->assertOk();
    }

    public function test_tenant_is_redirected_to_login(): void
    {
        // Arrange
        $tenant = $this->createTenant();

        // Act
        $response = $this->actingAs($tenant)->get('/admin');

        // Assert
        $response->assertRedirect(route('login'));
    }

    public function test_superadmin_with_permission_renders_dashboard(): void
    {
        // Arrange
        $superadmin = $this->createSuperadmin();

        // Act
        $response = $this->actingAs($superadmin)->get('/admin');

        // Assert
        $response->assertOk();
    }

    public function test_admin_without_permissions_receives_403(): void
    {
        // Arrange: admin role sin permisos
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $role = Role::findByName('admin', 'web');
        $role->syncPermissions([]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = User::factory()->admin()->create();
        $admin->assignRole($role);

        // Act
        $response = $this->actingAs($admin)->get('/admin');

        // Assert
        $response->assertForbidden();
    }

    public function test_superadmin_without_permissions_bypasses(): void
    {
        // Arrange: superadmin sin permisos explícitos
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $role = Role::findByName('superadmin', 'web');
        $role->syncPermissions([]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superadmin = User::factory()->superadmin()->create();
        $superadmin->assignRole($role);

        // Act
        $response = $this->actingAs($superadmin)->get('/admin');

        // Assert
        $response->assertOk();
    }

    public function test_admin_without_blogs_read_fails_gate_check(): void
    {
        // Arrange
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $role = Role::findByName('admin', 'web');
        $role->revokePermissionTo('blogs-read');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = User::factory()->admin()->create();
        $admin->assignRole($role);

        // Act + Assert
        $this->actingAs($admin);
        $this->assertFalse(Gate::check('blogs-read'));
    }
}
