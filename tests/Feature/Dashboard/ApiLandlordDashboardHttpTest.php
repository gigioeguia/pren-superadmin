<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * F1–F2 del spec-controladores-residuales-fase5: congela el contrato HTTP
 * actual del dashboard API de landlord ANTES de extraer DashboardService.
 * Tras la migración al service estos tests deben seguir en verde sin cambios
 * (la continuidad es la prueba de que el refactor preserva el contrato).
 *
 * @group smoke
 */
class ApiLandlordDashboardHttpTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles/permisos Spatie en la DB de testing (patrón de la suite smoke)
        $this->seed(PermissionSeeder::class);
    }

    private function actingLandlord(): User
    {
        $landlord = $this->createLandlord();
        Sanctum::actingAs($landlord, ['*']);

        return $landlord;
    }

    public function test_dashboard_index_returns_current_shape(): void
    {
        // Arrange
        $this->actingLandlord();

        // Act
        $response = $this->getJson('/api/v1/landlord/dashboard');

        // Assert
        $response->assertOk()->assertJsonStructure([
            'message',
            'data' => [
                'current_balance', 'total_withdraw', 'total_properties', 'total_tenant',
                'total_rent_paid', 'total_rent_due', 'total_application',
                'pending_application', 'processing_application', 'approve_application',
                'reject_application', 'pending_maintenance', 'processing_maintenance',
                'maintenance_cost',
            ],
        ]);
        $this->assertSame('Data fetched successfully', $response->json('message'));
        $this->assertSame(0, $response->json('data.total_properties'));
    }

    public function test_dashboard_property_chart_returns_current_shape(): void
    {
        // Arrange
        $this->actingLandlord();

        // Act
        $response = $this->getJson('/api/v1/landlord/dashboard/property-chart');

        // Assert
        $response->assertOk()->assertJsonStructure([
            'message',
            'data' => [
                'total_properties', 'occupied', 'maintenance', 'vacant',
                'occupied_percentage', 'vacant_percentage', 'maintenance_percentage',
            ],
        ]);
        $this->assertSame('Data fetched successfully', $response->json('message'));
    }

    public function test_dashboard_account_chart_monthly_returns_current_shape(): void
    {
        // Arrange
        $this->actingLandlord();

        // Act
        $response = $this->getJson('/api/v1/landlord/dashboard/account-chart?duration=monthly');

        // Assert
        $response->assertOk()->assertJsonStructure([
            'message',
            'data' => [
                'total_income', 'total_expense', 'profit_loss', 'profit_loss_percentage',
                'status', 'max_value', 'min_value', 'income_amount_data', 'expense_amount_data',
            ],
        ]);
        $this->assertSame('equal', $response->json('data.status'));
    }

    public function test_dashboard_account_chart_invalid_duration_returns_400(): void
    {
        // Arrange
        $this->actingLandlord();

        // Act
        $response = $this->getJson('/api/v1/landlord/dashboard/account-chart?duration=bogus');

        // Assert
        $response->assertStatus(400)->assertJson(['error' => 'Invalid duration']);
    }
}
