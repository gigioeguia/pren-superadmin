<?php

namespace Tests\Feature\Admin;

use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
