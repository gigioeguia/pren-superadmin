<?php

namespace Tests\Unit\Seeders;

use App\Models\Category;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DemoResetResolutionTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        app()->detectEnvironment(fn () => 'testing');

        parent::tearDown();
    }

    public function test_u4_demo_reset_deletes_only_is_demo_landlords(): void
    {
        app()->detectEnvironment(fn () => 'demo');

        Artisan::shouldReceive('call')
            ->once()
            ->with('db:seed', ['--class' => 'DemoSeeder'])
            ->andReturn(0);

        $demo = User::factory()->landlord()->create();
        $demo->forceFill(['role' => 'landlord', 'is_demo' => true])->save();

        $real = User::factory()->landlord()->create();

        $this->createLandlordRows($demo->id);
        $this->createLandlordRows($real->id);

        $this->assertTrue(demoReset());

        $this->assertSame(0, Property::where('landlord_id', $demo->id)->count());
        $this->assertSame(0, IncomeCategory::where('landlord_id', $demo->id)->count());
        $this->assertSame(0, Income::where('landlord_id', $demo->id)->count());
        $this->assertSame(0, ExpenseCategory::where('landlord_id', $demo->id)->count());
        $this->assertSame(0, Expense::where('landlord_id', $demo->id)->count());

        // el landlord NO demo (cualquier id) queda intacto
        $this->assertSame(1, Property::where('landlord_id', $real->id)->count());
        $this->assertSame(1, IncomeCategory::where('landlord_id', $real->id)->count());
        $this->assertSame(1, Income::where('landlord_id', $real->id)->count());
        $this->assertSame(1, ExpenseCategory::where('landlord_id', $real->id)->count());
        $this->assertSame(1, Expense::where('landlord_id', $real->id)->count());
    }

    private function createLandlordRows(int $landlordId): void
    {
        $category = Category::firstOrCreate(['name' => 'Probe category'], ['status' => 1]);

        Property::create([
            'landlord_id' => $landlordId,
            'category_id' => $category->id,
            'house_type_id' => null,
            'room_info' => null,
            'building_name' => 'Reset probe',
            'utility_deposit' => 0,
            'security_deposit' => 0,
            'sale_amount' => 0,
            'late_fee' => 0,
            'late_fee_after_days' => 0,
            'cover_image' => [],
            'status' => 1,
            'type' => 'rent',
            'rent_info' => null,
            'address_info' => null,
            'meta' => null,
        ]);

        $incomeCategory = IncomeCategory::create([
            'landlord_id' => $landlordId,
            'name' => 'Probe income '.$landlordId,
            'description' => 'probe',
            'status' => 1,
        ]);

        Income::create([
            'income_category_id' => $incomeCategory->id,
            'landlord_id' => $landlordId,
            'amount' => 1,
            'income_for' => 'probe',
            'payment_type' => 'cash',
            'reference_no' => 'PRB-I-'.$landlordId,
            'note' => null,
            'income_date' => '2025-01-01',
        ]);

        $expenseCategory = ExpenseCategory::create([
            'landlord_id' => $landlordId,
            'name' => 'Probe expense '.$landlordId,
            'description' => 'probe',
            'status' => 1,
        ]);

        Expense::create([
            'expense_category_id' => $expenseCategory->id,
            'landlord_id' => $landlordId,
            'amount' => 1,
            'expense_for' => 'probe',
            'payment_type' => 'cash',
            'reference_no' => 'PRB-E-'.$landlordId,
            'note' => null,
            'expense_date' => '2025-01-01',
        ]);
    }
}
