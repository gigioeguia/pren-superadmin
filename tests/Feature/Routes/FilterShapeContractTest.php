<?php

namespace Tests\Feature\Routes;

use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Models\PlanSubscribe;
use App\Models\User;
use App\Models\WithdrawMethod;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Spec 5.22 / L1 expandido: 11 filtros inline devolvían HTML directo;
 * custom-ajax.js espera JSON {data}. Contrato canónico: AJAX -> JSON {data},
 * no-AJAX -> redirect(url()->previous()).
 */
#[Group('smoke')]
class FilterShapeContractTest extends TestCase
{
    use CreatesUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    private function landlordWithSubscription(): User
    {
        $landlord = $this->createLandlord();
        PlanSubscribe::factory()->create(['user_id' => $landlord->id]);

        return $landlord;
    }

    private function actingAsAdminWith(string $permission): User
    {
        $admin = $this->createSuperadmin();
        $admin->givePermissionTo($permission);

        return $admin;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function assertFilterShape(User $actor, string $uri, array $payload = []): void
    {
        $this->actingAs($actor);

        $ajax = $this->post($uri, $payload, ['X-Requested-With' => 'XMLHttpRequest']);

        $ajax->assertOk();
        $ajax->assertJsonStructure(['data']);
        $this->assertIsString($ajax->json('data'));

        $nonAjax = $this->post($uri, $payload);
        $nonAjax->assertRedirect(url()->previous());
    }

    public function test_f1_admin_expense_categories_filter_returns_canonical_shape(): void
    {
        $admin = $this->actingAsAdminWith('expense-categories-read');
        ExpenseCategory::create(['name' => 'Filter EC', 'status' => 1]);

        $this->assertFilterShape($admin, '/admin/expense-categories/filter');
    }

    public function test_f2_landlord_expense_categories_filter_returns_canonical_shape(): void
    {
        $landlord = $this->landlordWithSubscription();
        ExpenseCategory::create(['landlord_id' => $landlord->id, 'name' => 'LL Filter EC', 'status' => 1]);

        $this->assertFilterShape($landlord, '/landlord/expense-categories/filter');
    }

    public function test_f3_admin_income_categories_filter_returns_canonical_shape(): void
    {
        $admin = $this->actingAsAdminWith('income-categories-read');
        IncomeCategory::create(['name' => 'Filter IC', 'status' => 1]);

        $this->assertFilterShape($admin, '/admin/income-categories/filter');
    }

    public function test_f4_landlord_income_categories_filter_returns_canonical_shape(): void
    {
        $landlord = $this->landlordWithSubscription();
        IncomeCategory::create(['landlord_id' => $landlord->id, 'name' => 'LL Filter IC', 'status' => 1]);

        $this->assertFilterShape($landlord, '/landlord/income-categories/filter');
    }

    public function test_f5_admin_withdraw_methods_filter_returns_canonical_shape(): void
    {
        $admin = $this->actingAsAdminWith('withdraw-methods-read');
        WithdrawMethod::create([
            'name' => 'Filter WM',
            'min_amount' => 1,
            'max_amount' => 100,
            'charge' => 0,
            'charge_type' => 'fixed',
            'status' => 1,
        ]);

        $this->assertFilterShape($admin, '/admin/withdraw-methods/filter');
    }

    public function test_f6_landlord_refund_request_filter_returns_canonical_shape(): void
    {
        $landlord = $this->landlordWithSubscription();

        $this->assertFilterShape($landlord, '/landlord/refund-request/filter');
    }

    public function test_f7_landlord_security_deposit_filter_returns_canonical_shape(): void
    {
        $landlord = $this->landlordWithSubscription();

        $this->assertFilterShape($landlord, '/landlord/security-deposits/filter');
    }

    public function test_f8_admin_property_filter_returns_canonical_shape(): void
    {
        $admin = $this->actingAsAdminWith('properties-read');

        $this->assertFilterShape($admin, '/admin/properties/filter');
    }

    public function test_f9_landlord_property_filter_returns_canonical_shape(): void
    {
        $landlord = $this->landlordWithSubscription();

        $this->assertFilterShape($landlord, '/landlord/properties/filter');
    }

    public function test_f10_landlord_rent_payment_filter_returns_canonical_shape(): void
    {
        $landlord = $this->landlordWithSubscription();

        $this->assertFilterShape($landlord, '/landlord/rent-payments/filter');
    }

    public function test_f11_admin_refund_request_report_filter_returns_canonical_shape(): void
    {
        $admin = $this->actingAsAdminWith('reports-read');

        $this->assertFilterShape($admin, '/admin/refund-request/filter');
    }
}
