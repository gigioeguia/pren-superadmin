<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\PreventRequestForgery;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Models\SecurityDeposit;
use App\Models\WithdrawMethod;
use App\Models\Currency;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Group;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * G1–G11 + F1–F2 del spec 5.17 (restauración CRUD 4.2): las 9 rutas
 * admin restauradas resuelven con su contrato observable pre-4.2
 * (filenames verbatim del blob 537a626^, shape {data} para el JS,
 * statusMessage verbatim) y las 16 declaraciones create/edit zombis
 * desaparecen sin romper el flujo de modales.
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

    /**
     * Crea un admin con rol 'admin' al que se le revoca un permiso
     * (patrón PermissionDenialSmokeTest).
     */
    private function adminWithout(string $permission): \App\Models\User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $role = Role::findByName('admin', 'web');
        if ($role->hasPermissionTo($permission)) {
            $role->revokePermissionTo($permission);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = \App\Models\User::factory()->admin()->create();
        $admin->assignRole($role);

        return $admin;
    }

    public function test_g1_expenses_exports_download_with_verbatim_filenames(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();

        // Act
        $excel = $this->actingAs($admin)->get(route('admin.expenses.excel'));
        $csv = $this->actingAs($admin)->get(route('admin.expenses.csv'));

        // Assert — filenames verbatim pre-4.2
        $excel->assertOk();
        $this->assertStringContainsString('admin-expenses.xlsx', $excel->headers->get('content-disposition'));
        $csv->assertOk();
        $this->assertStringContainsString('admin-expenses.csv', $csv->headers->get('content-disposition'));
    }

    public function test_g2_incomes_exports_download_with_verbatim_filenames(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();

        // Act
        $excel = $this->actingAs($admin)->get(route('admin.incomes.excel'));
        $csv = $this->actingAs($admin)->get(route('admin.incomes.csv'));

        // Assert
        $excel->assertOk();
        $this->assertStringContainsString('admin-incomes.xlsx', $excel->headers->get('content-disposition'));
        $csv->assertOk();
        $this->assertStringContainsString('admin-incomes.csv', $csv->headers->get('content-disposition'));
    }

    public function test_g3_security_deposits_exports_download_with_verbatim_filenames(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();

        // Act
        $excel = $this->actingAs($admin)->get(route('admin.security-deposits.excel'));
        $csv = $this->actingAs($admin)->get(route('admin.security-deposits.csv'));

        // Assert
        $excel->assertOk();
        $this->assertStringContainsString('security-deposits.xlsx', $excel->headers->get('content-disposition'));
        $csv->assertOk();
        $this->assertStringContainsString('security-deposits.csv', $csv->headers->get('content-disposition'));
    }

    public function test_g4_security_deposits_filter_returns_json_rows(): void
    {
        // Arrange — el branch Admin del service no filtra (Q14-a verbatim):
        // paginación sobre todas las filas. El hook creating del modelo genera
        // invoice_no determinista (00001...), lo usamos como ancla del render.
        $admin = $this->createSuperadmin();
        $deposit = SecurityDeposit::factory()->create();

        // Act
        $response = $this->actingAs($admin)->postJson('/admin/security-deposits/filter', [
            'search' => 'ignored-by-contract',
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        // Assert
        $response->assertOk()->assertJsonStructure(['data']);
        $this->assertStringContainsString($deposit->invoice_no, $response->json('data'));
    }

    public function test_g5_security_deposits_filter_redirects_without_xhr(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();

        // Act
        $response = $this->actingAs($admin)->postJson('/admin/security-deposits/filter');

        // Assert — shape del trait: sin XHR, redirect a la URL previa
        $response->assertRedirect();
    }

    public function test_g6_expense_categories_status_flips_and_returns_verbatim_message(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();
        $category = ExpenseCategory::create(['name' => 'Utilities', 'status' => 1]);

        // Act
        $response = $this->actingAs($admin)->postJson("/admin/expense-categories/status/{$category->id}", ['status' => 0]);

        // Assert — statusMessage verbatim pre-4.2 (sin __())
        $response->assertOk()->assertJson(['message' => 'Expense Category']);
        $this->assertSame(0, (int) $category->fresh()->status);
    }

    public function test_g7_income_categories_status_flips_and_returns_verbatim_message(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();
        $category = IncomeCategory::create(['name' => 'Rent Income', 'status' => 0]);

        // Act
        $response = $this->actingAs($admin)->postJson("/admin/income-categories/status/{$category->id}", ['status' => 1]);

        // Assert
        $response->assertOk()->assertJson(['message' => 'Income Category']);
        $this->assertSame(1, (int) $category->fresh()->status);
    }

    public function test_g8_withdraw_methods_status_flips_and_returns_verbatim_message(): void
    {
        // Arrange
        $admin = $this->createSuperadmin();
        $method = WithdrawMethod::create([
            'name' => 'Bank Transfer',
            'currency_id' => Currency::factory()->create()->id,
            'min_amount' => 0,
            'max_amount' => 100,
            'charge' => 0,
            'charge_type' => 'fixed',
            'instructions' => 'Go to the bank',
            'meta' => ['label' => null, 'input' => []],
            'status' => 1,
        ]);

        // Act
        $response = $this->actingAs($admin)->postJson("/admin/withdraw-methods/status/{$method->id}", ['status' => 0]);

        // Assert
        $response->assertOk()->assertJson(['message' => 'withdrawMethod']);
        $this->assertSame(0, (int) $method->fresh()->status);
    }

    public function test_g9_admin_role_reaches_security_deposits_after_seeder_fix(): void
    {
        // Arrange — rol 'admin' real (no superadmin: Gate::before enmascara
        // fallos de permiso). Pre-fix Q11 este rol recibía 403 hasta en el
        // index vivo (seeder singular vs controller plural).
        $admin = $this->createAdmin();

        // Act/Assert
        $this->actingAs($admin)->get(route('admin.security-deposits.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.security-deposits.excel'))->assertOk();
        $this->actingAs($admin)->postJson('/admin/security-deposits/filter', [], ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
    }

    public function test_g10_security_deposits_export_denied_without_permission(): void
    {
        // Arrange
        $admin = $this->adminWithout('security-deposits-read');

        // Act
        $response = $this->actingAs($admin)->get(route('admin.security-deposits.excel'));

        // Assert
        $response->assertForbidden();
    }

    public function test_g11_demo_blocks_restored_status_routes(): void
    {
        // Arrange — las 3 status restauradas ya estaban en la lista DemoMode;
        // sin cambios de listas, siguen bloqueando en demo. En entorno demo el
        // CSRF deja de saltarse (runningUnitTests false) → sin middleware.
        app()->detectEnvironment(fn () => 'demo');
        $admin = $this->createAdmin();

        // Act/Assert
        foreach (['expense-categories', 'income-categories', 'withdraw-methods'] as $resource) {
            $response = $this->actingAs($admin)
                ->withoutMiddleware(PreventRequestForgery::class)
                ->postJson("/admin/{$resource}/status/1", ['status' => 1]);

            $response->assertStatus(499);
            $response->assertJson(['message' => 'This action is disabled in demo mode.']);
        }
    }

    public function test_f1_removed_create_edit_routes_are_gone(): void
    {
        // Arrange — las 32 declaraciones create/edit (verbos resource zombis
        // que nunca tuvieron método; los modales postean a store/update vivos).
        $removed = [
            'admin.amenities.create', 'admin.amenities.edit',
            'admin.expense-categories.create', 'admin.expense-categories.edit',
            'admin.expenses.create', 'admin.expenses.edit',
            'admin.facilities.create', 'admin.facilities.edit',
            'admin.house-types.create', 'admin.house-types.edit',
            'admin.income-categories.create', 'admin.income-categories.edit',
            'admin.incomes.create', 'admin.incomes.edit',
            'admin.refund-reason.create', 'admin.refund-reason.edit',
            'landlord.amenities.create', 'landlord.amenities.edit',
            'landlord.expense-categories.create', 'landlord.expense-categories.edit',
            'landlord.expenses.create', 'landlord.expenses.edit',
            'landlord.facilities.create', 'landlord.facilities.edit',
            'landlord.income-categories.create', 'landlord.income-categories.edit',
            'landlord.incomes.create', 'landlord.incomes.edit',
            'landlord.labors.create', 'landlord.labors.edit',
            'landlord.withdraw-method.create', 'landlord.withdraw-method.edit',
        ];

        // Act/Assert
        foreach ($removed as $name) {
            $this->assertNull(Route::getRoutes()->getByName($name), "Route [{$name}] should have been removed.");
        }
    }

    public function test_f2_amenity_modal_flow_survives_route_removal(): void
    {
        // Arrange — el form del modal postea directo a store (vivo)
        $admin = $this->createSuperadmin();
        $landlord = $this->createLandlord();

        // Act
        $index = $this->actingAs($admin)->get(route('admin.amenities.index'));
        $store = $this->actingAs($admin)->postJson(route('admin.amenities.store'), [
            'landlord_id' => $landlord->id,
            'name' => 'Modal Amenity',
            'status' => 1,
        ]);

        // Assert — index renderiza el modal @include; store crea la fila
        $index->assertOk();
        $store->assertOk();
        $this->assertDatabaseHas('amenities', ['name' => 'Modal Amenity', 'landlord_id' => $landlord->id]);
    }
}
