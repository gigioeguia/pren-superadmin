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

    /**
     * @group characterization
     */
    public function test_landlord_passes_broken_admin_middleware(): void
    {
        // Expected to fail after Phase 1 (Seg-2 fix) — AdminMiddleware has a
        // precedence bug: auth()->check() && role!=='landlord' || role!=='tenant'.
        // Landlords must be redirected to login. The 403 below comes from the
        // Spatie permission middleware, NOT from AdminMiddleware: the guard
        // was already bypassed.
        // Arrange
        $landlord = $this->createLandlord();

        // Act
        $response = $this->actingAs($landlord)->get('/admin');

        // Assert
        $response->assertStatus(403);
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

    /**
     * @group characterization
     */
    public function test_tenant_passes_broken_admin_middleware(): void
    {
        // Expected to fail after Phase 1 (Seg-2 fix) — same precedence bug
        // as G2. Tenants must be redirected to login; the 403 below proves
        // AdminMiddleware let the tenant through.
        // Arrange
        $tenant = $this->createTenant();

        // Act
        $response = $this->actingAs($tenant)->get('/admin');

        // Assert
        $response->assertStatus(403);
    }
}
