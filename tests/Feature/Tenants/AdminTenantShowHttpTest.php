<?php

namespace Tests\Feature\Tenants;

use App\Models\UserDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * F1 del spec-controladores-residuales-fase5 (D1): contrato del show
 * admin congelado pre-recorte del TenantController (U1) — la query
 * eager-loaded se mueve a TenantService::loadForShow sin cambio de
 * shape. Nota: `show` no está hoy en el gate `tenants-read` (quirk
 * preexistente, documentado como hallazgo lateral del slice D1).
 */
class AdminTenantShowHttpTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    public function test_f1_admin_tenant_show_renders_view(): void
    {
        // Arrange — la vista lee user_details-> sin null-safe: fila requerida
        $admin = $this->createAdmin();
        $tenant = $this->createTenant();
        UserDetail::create(['user_id' => $tenant->id]);

        // Act
        $response = $this->actingAs($admin)->get("/admin/tenants/{$tenant->id}");

        // Assert
        $response->assertOk()->assertViewIs('admin.tenants.view');
        $response->assertSee($tenant->name);
    }
}
