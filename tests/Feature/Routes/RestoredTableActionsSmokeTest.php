<?php

namespace Tests\Feature\Routes;

use App\Models\PlanSubscribe;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Rutas muertas/rotas de la Fase 4.2 restauradas (spec residuales 5.13,
 * P2 del paraguas): los endpoints filter/excel/csv de tenants y
 * rent-payments a los que las vistas aún apuntaban (500 al usarlos)
 * vuelven a resolver, con el shape que exige el JS (custom-ajax.js:
 * dataType json + res.data). Los OTP de tenants admin, sin referencias,
 * se eliminan.
 *
 * @group smoke
 */
class RestoredTableActionsSmokeTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles/permisos Spatie en la DB de testing (patrón de la suite smoke)
        $this->seed(PermissionSeeder::class);
    }

    private function landlordWithSubscription(): User
    {
        $landlord = $this->createLandlord();
        PlanSubscribe::factory()->create(['user_id' => $landlord->id]);

        return $landlord;
    }

    public function test_restored_routes_resolve_to_existing_methods(): void
    {
        // Arrange — las 11 rutas restauradas por 4.2
        $names = [
            'admin.tenants.filter', 'admin.tenants.excel', 'admin.tenants.csv',
            'landlord.tenants.filter', 'landlord.tenants.excel', 'landlord.tenants.csv',
            'admin.rent-payments.filter', 'admin.rent-payments.excel', 'admin.rent-payments.csv',
            'landlord.rent-payments.excel', 'landlord.rent-payments.csv',
        ];

        // Assert — guard de reflexión: registradas y con método existente
        foreach ($names as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, "Route [{$name}] is not registered.");

            [$class, $method] = explode('@', $route->getActionName());
            $this->assertTrue(
                method_exists($class, $method),
                "Route [{$name}] points to missing method [{$class}@{$method}]."
            );
        }
    }

    public function test_dead_otp_routes_are_removed(): void
    {
        // Arrange/Act/Assert — sin referencias en vistas (grep del slice)
        $this->assertNull(Route::getRoutes()->getByName('admin.tenants.otp-submit'));
        $this->assertNull(Route::getRoutes()->getByName('admin.tenants.otp-resend'));
    }

    public function test_admin_tenants_filter_returns_json_rows(): void
    {
        // Arrange — nombre fijo: los nombres de faker con apóstrofe se
        // escapan a &#39; en el HTML renderizado y rompen el assert.
        $admin = $this->createSuperadmin();
        $landlord = $this->createLandlord();
        $tenant = User::factory()->tenant()->create([
            'name' => 'Admin Filter Tenant',
            'landlord_id' => $landlord->id,
        ]);

        // Act
        $response = $this->actingAs($admin)->postJson('/admin/tenants/filter', [
            'search' => $tenant->name,
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        // Assert
        $response->assertOk()->assertJsonStructure(['data']);
        $this->assertStringContainsString($tenant->name, $response->json('data'));
    }

    public function test_landlord_tenants_filter_scopes_to_own_tenants(): void
    {
        // Arrange
        $landlordA = $this->landlordWithSubscription();
        $landlordB = $this->createLandlord();
        $own = User::factory()->tenant()->create(['name' => 'Scoped Filter Tenant', 'landlord_id' => $landlordA->id]);
        $foreign = User::factory()->tenant()->create(['name' => 'Foreign Filter Tenant', 'landlord_id' => $landlordB->id]);

        // Act
        $response = $this->actingAs($landlordA)->postJson('/landlord/tenants/filter', [
            'search' => '',
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        // Assert
        $response->assertOk()->assertJsonStructure(['data']);
        $this->assertStringContainsString($own->name, $response->json('data'));
        $this->assertStringNotContainsString($foreign->name, $response->json('data'));
    }

    public function test_admin_rent_payments_filter_returns_json_rows(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();

        // Act
        $response = $this->actingAs($admin)->postJson('/admin/rent-payments/filter', [
            'search' => '',
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        // Assert
        $response->assertOk()->assertJsonStructure(['data']);
    }

    public function test_admin_tenants_exports_download(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();

        // Act/Assert
        $this->actingAs($admin)->get(route('admin.tenants.excel'))->assertOk();
        $this->actingAs($admin)->get(route('admin.tenants.csv'))->assertOk();
    }

    public function test_landlord_tenants_exports_download(): void
    {
        // Arrange
        $landlord = $this->landlordWithSubscription();

        // Act/Assert
        $this->actingAs($landlord)->get(route('landlord.tenants.excel'))->assertOk();
        $this->actingAs($landlord)->get(route('landlord.tenants.csv'))->assertOk();
    }

    public function test_rent_payments_exports_download(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();
        $landlord = $this->landlordWithSubscription();

        // Act/Assert
        $this->actingAs($admin)->get(route('admin.rent-payments.excel'))->assertOk();
        $this->actingAs($admin)->get(route('admin.rent-payments.csv'))->assertOk();
        $this->actingAs($landlord)->get(route('landlord.rent-payments.excel'))->assertOk();
        $this->actingAs($landlord)->get(route('landlord.rent-payments.csv'))->assertOk();
    }
}
