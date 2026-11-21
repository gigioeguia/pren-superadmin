<?php

namespace Tests\Feature\Seeders;

use App\Models\Amenity;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Facility;
use App\Models\Faq;
use App\Models\Feature;
use App\Models\Gateway;
use App\Models\Highlight;
use App\Models\HouseType;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\Language;
use App\Models\Option;
use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\Property;
use App\Models\PropertyDetail;
use App\Models\Rent;
use App\Models\Review;
use App\Models\Testimonial;
use App\Models\Unit;
use App\Models\User;
use App\Models\WithdrawMethod;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseSeederIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_f1_double_seed_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);

        $first = $this->datasetCounts();

        $this->seed(DatabaseSeeder::class);

        // la segunda pasada no añade ni una fila en ninguna tabla
        $this->assertSame($first, $this->datasetCounts());

        // dataset demo completo, scoped por clave natural (inmune a filas
        // preexistentes de tests sin RefreshDatabase)
        $this->assertSame(4, User::whereIn('email', ['superadmin@acnoo.com', 'admin@acnoo.com', 'landlord@acnoo.com', 'tenant@acnoo.com'])->count());

        $demo = User::demoLandlords()->first();
        $this->assertNotNull($demo);
        $this->assertSame(8, Property::where('landlord_id', $demo->id)->count());

        $this->assertSame(1, Currency::where('code', 'USD')->where('is_default', 1)->count());
        $this->assertSame(1, Currency::where('code', 'XOF')->count());
        $this->assertSame(12, Gateway::whereIn('name', ['Stripe', 'Mollie', 'FedaPay', 'Paystack', 'Razorpay', 'Instamojo', 'Toyyibpay', 'Flutterwave', 'Thawani', 'Tap Payment', 'Billplz', 'Offline'])->count());
        $this->assertSame(3, Plan::whereIn('subscriptionName', ['Free', 'Standard', 'Economy'])->count());
        $this->assertSame(8, Option::whereIn('key', ['general', 'term-condition', 'privacy-policy', 'service-charge', 'reminder-messages', 'manage-pages', 'email-varification', 'version'])->count());
        $this->assertSame(1, Language::where('code', 'en')->where('is_default', 1)->count());
    }

    public function test_f4_null_scope_finance_rows_reference_own_categories(): void
    {
        $this->seed(DatabaseSeeder::class);

        $nullIncomes = Income::whereNull('landlord_id')->get();
        $this->assertCount(3, $nullIncomes);

        foreach ($nullIncomes as $income) {
            $category = IncomeCategory::find($income->income_category_id);

            $this->assertNotNull($category, 'Null-scope income references a missing category');
            $this->assertNull($category->landlord_id, 'Null-scope income references a demo landlord category');
        }

        $nullExpenses = Expense::whereNull('landlord_id')->get();
        $this->assertCount(5, $nullExpenses);

        foreach ($nullExpenses as $expense) {
            $category = ExpenseCategory::find($expense->expense_category_id);

            $this->assertNotNull($category, 'Null-scope expense references a missing category');
            $this->assertNull($category->landlord_id, 'Null-scope expense references a demo landlord category');
        }
    }

    public function test_f6_permission_seeder_is_idempotent(): void
    {
        $usersBefore = User::count();

        $this->seed(PermissionSeeder::class);

        $roleCount = DB::table('roles')->count();
        $permissionCount = DB::table('permissions')->count();

        $this->seed(PermissionSeeder::class);

        $this->assertSame($usersBefore + 2, User::count());
        $this->assertSame(1, User::whereEmail('superadmin@acnoo.com')->count());
        $this->assertSame(1, User::whereEmail('admin@acnoo.com')->count());
        $this->assertSame($roleCount, DB::table('roles')->count());
        $this->assertSame($permissionCount, DB::table('permissions')->count());
    }

    /**
     * @return array<string, int>
     */
    private function datasetCounts(): array
    {
        return [
            'users' => User::count(),
            'currencies' => Currency::count(),
            'languages' => Language::count(),
            'gateways' => Gateway::count(),
            'plans' => Plan::count(),
            'plan_subscribes' => PlanSubscribe::count(),
            'categories' => Category::count(),
            'house_types' => HouseType::count(),
            'facilities' => Facility::count(),
            'amenities' => Amenity::count(),
            'properties' => Property::count(),
            'property_details' => PropertyDetail::count(),
            'units' => Unit::count(),
            'withdraw_methods' => WithdrawMethod::count(),
            'options' => Option::count(),
            'rents' => Rent::count(),
            'reviews' => Review::count(),
            'blogs' => Blog::count(),
            'faqs' => Faq::count(),
            'features' => Feature::count(),
            'testimonials' => Testimonial::count(),
            'clients' => Client::count(),
            'income_categories' => IncomeCategory::count(),
            'incomes' => Income::count(),
            'expense_categories' => ExpenseCategory::count(),
            'expenses' => Expense::count(),
            'highlights' => Highlight::count(),
        ];
    }
}
