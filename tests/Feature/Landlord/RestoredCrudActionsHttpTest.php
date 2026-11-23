<?php

namespace Tests\Feature\Landlord;

use App\Http\Middleware\PreventRequestForgery;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Models\PlanSubscribe;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * H1–H11 + F3 del spec 5.17 (restauración CRUD 4.2): las 13 rutas
 * landlord restauradas resuelven con su contrato observable pre-4.2
 * — filenames y mensajes verbatim del blob 537a626^, deleteMany
 * scoped del service (desviación security-positive Q7) y el lookup
 * unscoped de status preservado (Seg-5, caracterizado en H7).
 */
#[Group('smoke')]
class RestoredCrudActionsHttpTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    protected function tearDown(): void
    {
        app()->detectEnvironment(fn () => 'testing');

        parent::tearDown();
    }

    private function subscribedLandlord(): User
    {
        return $this->landlordWithPlan(now()->addMonth());
    }

    private function expiredLandlord(): User
    {
        return $this->landlordWithPlan(now()->subDay());
    }

    private function landlordWithPlan(\DateTimeInterface $willExpire): User
    {
        $landlord = $this->createLandlord(['will_expire' => $willExpire]);
        PlanSubscribe::factory()->create(['user_id' => $landlord->id]);

        return $landlord;
    }

    public function test_h1_expenses_exports_download_with_verbatim_filenames(): void
    {
        // Arrange
        $landlord = $this->subscribedLandlord();

        // Act
        $excel = $this->actingAs($landlord)->get(route('landlord.expenses.excel'));
        $csv = $this->actingAs($landlord)->get(route('landlord.expenses.csv'));

        // Assert — filenames verbatim pre-4.2
        $excel->assertOk();
        $this->assertStringContainsString('landlord-expenses.xlsx', $excel->headers->get('content-disposition'));
        $csv->assertOk();
        $this->assertStringContainsString('landlord-expenses.csv', $csv->headers->get('content-disposition'));
    }

    public function test_h2_incomes_exports_download_with_verbatim_filenames(): void
    {
        // Arrange
        $landlord = $this->subscribedLandlord();

        // Act
        $excel = $this->actingAs($landlord)->get(route('landlord.incomes.excel'));
        $csv = $this->actingAs($landlord)->get(route('landlord.incomes.csv'));

        // Assert
        $excel->assertOk();
        $this->assertStringContainsString('landlord-incomes.xlsx', $excel->headers->get('content-disposition'));
        $csv->assertOk();
        $this->assertStringContainsString('landlord-incomes.csv', $csv->headers->get('content-disposition'));
    }

    public function test_h3_security_deposits_exports_download_with_verbatim_filenames(): void
    {
        // Arrange
        $landlord = $this->subscribedLandlord();

        // Act
        $excel = $this->actingAs($landlord)->get(route('landlord.security-deposits.excel'));
        $csv = $this->actingAs($landlord)->get(route('landlord.security-deposits.csv'));

        // Assert
        $excel->assertOk();
        $this->assertStringContainsString('security-deposits.xlsx', $excel->headers->get('content-disposition'));
        $csv->assertOk();
        $this->assertStringContainsString('security-deposits.csv', $csv->headers->get('content-disposition'));
    }

    public function test_h4_refund_request_exports_download_with_verbatim_filenames(): void
    {
        // Arrange
        $landlord = $this->subscribedLandlord();

        // Act
        $excel = $this->actingAs($landlord)->get(route('landlord.refund-request.excel'));
        $csv = $this->actingAs($landlord)->get(route('landlord.refund-request.csv'));

        // Assert
        $excel->assertOk();
        $this->assertStringContainsString('refund-request.xlsx', $excel->headers->get('content-disposition'));
        $csv->assertOk();
        $this->assertStringContainsString('refund-request.csv', $csv->headers->get('content-disposition'));
    }

    public function test_h5_expired_landlord_can_export_but_index_redirects(): void
    {
        // Arrange — desviación D2 del spec: la lista de ExpiredMiddleware es
        // de BLOQUEO y los exports nunca estuvieron en ella (verbatim pre-4.2);
        // la asimetría index-bloqueado/exports-abiertos queda congelada aquí.
        $landlord = $this->expiredLandlord();

        // Act
        $index = $this->actingAs($landlord)->get(route('landlord.refund-request.index'));
        $excel = $this->actingAs($landlord)->get(route('landlord.refund-request.excel'));
        $csv = $this->actingAs($landlord)->get(route('landlord.refund-request.csv'));

        // Assert
        $index->assertRedirect(route('landlord.subscriptions.index'));
        $excel->assertOk();
        $csv->assertOk();
    }

    public function test_h6_expense_categories_status_flips_own_category(): void
    {
        // Arrange
        $landlord = $this->subscribedLandlord();
        $category = ExpenseCategory::create(['name' => 'Utilities', 'landlord_id' => $landlord->id, 'status' => 1]);

        // Act
        $response = $this->actingAs($landlord)->postJson("/landlord/expense-categories/status/{$category->id}", ['status' => 0]);

        // Assert — statusMessage verbatim pre-4.2 (sin __())
        $response->assertOk()->assertJson(['message' => 'Expense Category']);
        $this->assertSame(0, (int) $category->fresh()->status);
    }

    #[Group('characterization')]
    public function test_h7_status_updates_foreign_category_without_ownership_check(): void
    {
        // Expected to fail after policies cover categories (Seg-5 fix).
        // Arrange — el lookup unscoped es contrato del trait, preservado
        // por el spec 5.17 §5 (prohibido el scoping unilateral).
        $landlordA = $this->subscribedLandlord();
        $landlordB = $this->createLandlord();
        $foreign = ExpenseCategory::create(['name' => 'Foreign Cat', 'landlord_id' => $landlordB->id, 'status' => 1]);

        // Act
        $response = $this->actingAs($landlordA)->postJson("/landlord/expense-categories/status/{$foreign->id}", ['status' => 0]);

        // Assert — caracterización: flip sobre fila ajena
        $response->assertOk()->assertJson(['message' => 'Expense Category']);
        $this->assertSame(0, (int) $foreign->fresh()->status);
    }

    public function test_h8_expense_categories_delete_all_removes_own_rows(): void
    {
        // Arrange
        $landlord = $this->subscribedLandlord();
        $own = ExpenseCategory::create(['name' => 'Own Cat', 'landlord_id' => $landlord->id, 'status' => 1]);

        // Act
        $response = $this->actingAs($landlord)->postJson('/landlord/expense-categories/delete-all', [
            'ids' => [$own->id],
        ]);

        // Assert — mensaje verbatim pre-4.2 (con __())
        $response->assertOk()->assertJson([
            'message' => __('Selected Expense Category Deleted Successfully'),
            'redirect' => route('landlord.expense-categories.index'),
        ]);
        $this->assertDatabaseMissing('expense_categories', ['id' => $own->id]);
    }

    public function test_h9_expense_categories_delete_all_skips_foreign_rows(): void
    {
        // Arrange — deleteMany scoped del service: desviación
        // security-positive vs el unscoped verbatim del trait (Q7).
        $landlordA = $this->subscribedLandlord();
        $landlordB = $this->createLandlord();
        $own = ExpenseCategory::create(['name' => 'Scoped Own', 'landlord_id' => $landlordA->id, 'status' => 1]);
        $foreign = ExpenseCategory::create(['name' => 'Scoped Foreign', 'landlord_id' => $landlordB->id, 'status' => 1]);

        // Act
        $response = $this->actingAs($landlordA)->postJson('/landlord/expense-categories/delete-all', [
            'ids' => [$own->id, $foreign->id],
        ]);

        // Assert — propias borradas, ajenas intactas
        $response->assertOk();
        $this->assertDatabaseMissing('expense_categories', ['id' => $own->id]);
        $this->assertDatabaseHas('expense_categories', ['id' => $foreign->id]);
    }

    public function test_h10_income_categories_status_and_delete_all_mirror(): void
    {
        // Arrange
        $landlord = $this->subscribedLandlord();
        $landlordB = $this->createLandlord();
        $own = IncomeCategory::create(['name' => 'Own Income Cat', 'landlord_id' => $landlord->id, 'status' => 1]);
        $foreign = IncomeCategory::create(['name' => 'Foreign Income Cat', 'landlord_id' => $landlordB->id, 'status' => 1]);

        // Act — el flip se aserta ANTES del deleteAll (borra la fila propia)
        $status = $this->actingAs($landlord)->postJson("/landlord/income-categories/status/{$own->id}", ['status' => 0]);

        // Assert — espejo de H6 (el status comparte la clase Seg-5 de H7)
        $status->assertOk()->assertJson(['message' => 'Income Category']);
        $this->assertSame(0, (int) $own->fresh()->status);

        // Act — espejo de H8/H9
        $deleteAll = $this->actingAs($landlord)->postJson('/landlord/income-categories/delete-all', [
            'ids' => [$own->id, $foreign->id],
        ]);

        // Assert
        $deleteAll->assertOk()->assertJson([
            'message' => __('Selected Income Category Deleted Successfully'),
            'redirect' => route('landlord.income-categories.index'),
        ]);
        $this->assertDatabaseMissing('income_categories', ['id' => $own->id]);
        $this->assertDatabaseHas('income_categories', ['id' => $foreign->id]);
    }

    public function test_h11_demo_blocks_restored_status_and_delete_all_routes(): void
    {
        // Arrange — las 4 restauradas ya están en la lista DemoMode (sin
        // cambios); el branch landlord exige users.is_demo. En entorno demo
        // el CSRF deja de saltarse → sin middleware (patrón DemoModeTest).
        app()->detectEnvironment(fn () => 'demo');
        $landlord = $this->createLandlord(['is_demo' => true]);

        // Act/Assert
        $routes = [
            '/landlord/expense-categories/status/1',
            '/landlord/expense-categories/delete-all',
            '/landlord/income-categories/status/1',
            '/landlord/income-categories/delete-all',
        ];
        foreach ($routes as $uri) {
            $response = $this->actingAs($landlord)
                ->withoutMiddleware(PreventRequestForgery::class)
                ->postJson($uri, ['status' => 1]);

            $response->assertStatus(499);
            $response->assertJson(['message' => 'This action is disabled in demo account. Please sign up your own account for full access.']);
        }
    }

    public function test_f3_labor_modal_flow_survives_route_removal(): void
    {
        // Arrange — el form del modal postea directo a store (vivo)
        $landlord = $this->subscribedLandlord();

        // Act
        $index = $this->actingAs($landlord)->get(route('landlord.labors.index'));
        $store = $this->actingAs($landlord)->postJson(route('landlord.labors.store'), [
            'name' => 'Modal Labor',
            'email' => 'modal.labor@example.com',
            'phone' => '12345678',
            'salary' => 100,
        ]);

        // Assert — index renderiza el modal @include; store crea la fila
        $index->assertOk();
        $store->assertOk();
        $this->assertDatabaseHas('labors', ['name' => 'Modal Labor', 'landlord_id' => $landlord->id]);
    }
}
