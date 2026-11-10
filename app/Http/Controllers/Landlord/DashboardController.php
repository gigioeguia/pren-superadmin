<?php

namespace App\Http\Controllers\Landlord;

use App\Models\User;
use App\Models\Property;
use App\Models\Withdraw;
use App\Models\Application;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use App\Models\Income;
use App\Models\Expense;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $property = Property::where('landlord_id', auth()->id())->get('id');

        $applications = Application::with(['tenant:id,name,email,phone', 'property:id,puid', 'property.propertyDetail:property_id,property_info'])
            ->whereIn('property_id', $property)
            ->latest()->limit(5)
            ->get();

        $maintenances = Maintenance::with(['tenant:id,name,phone', 'labor:id,name', 'property:id', 'property.propertyDetail:property_id,property_info'])
            ->whereIn('property_id', $property)
            ->latest()->limit(5)
            ->get();

        return view('landlord.dashboard', compact('applications', 'maintenances'));
    }

    public function getDashboardData()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $lastMonth = Carbon::now()->subMonth()->month;
        $lastMonthYear = Carbon::now()->subMonth()->year;

        // Tenant counts
        $totalTenant = User::where('landlord_id', auth()->id())->where('role', 'tenant')->count();
        $lastMonthTenant = User::where('landlord_id', auth()->id())
            ->where('role', 'tenant')
            ->whereMonth('created_at', $lastMonth)
            ->whereYear('created_at', $lastMonthYear)
            ->count();
        $thisMonthTenant = User::where('landlord_id', auth()->id())
            ->where('role', 'tenant')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();

        // Property counts
        $totalProperty = Property::where('landlord_id', auth()->id())->count();
        $lastMonthProperty = Property::where('landlord_id', auth()->id())
            ->whereMonth('created_at', $lastMonth)
            ->whereYear('created_at', $lastMonthYear)
            ->count();
        $thisMonthProperty = Property::where('landlord_id', auth()->id())
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();

        // Withdraw sums
        $totalWithdraw = Withdraw::where('user_id', auth()->id())
            ->where('status', 'approved')
            ->sum('amount');
        $lastMonthWithdraw = Withdraw::where('user_id', auth()->id())
            ->where('status', 'approved')
            ->whereMonth('created_at', $lastMonth)
            ->whereYear('created_at', $lastMonthYear)
            ->sum('amount');
        $thisMonthWithdraw = Withdraw::where('user_id', auth()->id())
            ->where('status', 'approved')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('amount');

        // Compare and set arrows
        $tenantArrow = getArrow($lastMonthTenant, $thisMonthTenant);
        $propertyArrow = getArrow($lastMonthProperty, $thisMonthProperty);
        $withdrawArrow = getArrow($lastMonthWithdraw, $thisMonthWithdraw);

        $data = [
            'total_tenant' => $totalTenant,
            'last_month_total_tenant' => $lastMonthTenant,
            'tenant_arrow' => $tenantArrow,

            'total_property' => $totalProperty,
            'last_month_total_property' => $lastMonthProperty,
            'property_arrow' => $propertyArrow,

            'total_withdraw' => currency_format($totalWithdraw),
            'last_month_total_withdraw' => currency_format($lastMonthWithdraw),
            'withdraw_arrow' => $withdrawArrow,
        ];

        return response()->json($data);
    }

    public function property_chart(Request $request)
    {
        $year = $request->get('year', date('Y'));

        $total_property = Property::where('landlord_id', auth()->id())->whereYear('created_at', $year)->count();
        $pending = Property::where('landlord_id', auth()->id())->whereYear('created_at', $year)->where('status', 0)->count();
        $approved = Property::where('landlord_id', auth()->id())->whereYear('created_at', $year)->where('status', 1)->count();
        $rejected = Property::where('landlord_id', auth()->id())->whereYear('created_at', $year)->where('status', 3)->count();

        return response()->json([
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
            'total_property' => $total_property,
        ]);
    }


    public function account_chart(Request $request)
    {
        $year = $request->input('year', date('Y'));

        $incomeData = Income::where('landlord_id', auth()->id())
            ->whereYear('income_date', $year)
            ->selectRaw('MONTH(income_date) as month, SUM(amount) as total_income')
            ->groupBy('month')
            ->pluck('total_income', 'month')
            ->toArray();


        $expenseData = Expense::where('landlord_id', auth()->id())
            ->whereYear('expense_date', $year)
            ->selectRaw('MONTH(expense_date) as month, SUM(amount) as total_expense')
            ->groupBy('month')
            ->pluck('total_expense', 'month')
            ->toArray();

        // Fill months 1-12 with zero if no data
        $total_income = [];
        $total_expense = [];
        $total_balance = [];

        for ($month = 1; $month <= 12; $month++) {
            $income = isset($incomeData[$month]) ? (float) $incomeData[$month] : 0;
            $expense = isset($expenseData[$month]) ? (float) $expenseData[$month] : 0;
            $balance = $income - $expense;

            $total_income[] = $income;
            $total_expense[] = $expense;
            $total_balance[] = $balance;
        }

        return response()->json([
            'total_income' => $total_income,
            'total_expense' => $total_expense,
            'total_balance' => $total_balance,
        ]);
    }
}
