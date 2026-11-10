<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Income;
use App\Models\Property;
use App\Models\Withdraw;
use Illuminate\Http\Request;
use App\Models\PlanSubscribe;
use App\Models\RefundRequest;
use App\Http\Controllers\Controller;
use App\Models\Expense;

class AcnooAdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:dashboard-read')->only('index');
    }

    public function index()
    {
        $withdraws = Withdraw::with(['account_info:id,method_id,infos', 'account_info.withdraw_method:id,charge', 'user:id,name'])->latest()->take(3)->get();
        $refund_requests = RefundRequest::select('id', 'invoice_no', 'tenant_id', 'landlord_id', 'property_id', 'deposit_id', 'status', 'created_at')->with('property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'tenant:id,name', 'landlord:id,name', 'deposit:id,total_amount')->latest()->take(3)->get();

        return view('admin.dashboard', compact('withdraws', 'refund_requests'));
    }

    public function getDashboardData()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $lastMonth = Carbon::now()->subMonth()->month;
        $lastMonthYear = Carbon::now()->subMonth()->year;

        // Counts
        $totalLandlord = User::where('role', 'landlord')->count();
        $lastMonthLandlord = User::where('role', 'landlord')->whereMonth('created_at', $lastMonth)->whereYear('created_at', $lastMonthYear)->count();
        $thisMonthLandlord = User::where('role', 'landlord')->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->count();

        $totalTenant = User::where('role', 'tenant')->count();
        $lastMonthTenant = User::where('role', 'tenant')->whereMonth('created_at', $lastMonth)->whereYear('created_at', $lastMonthYear)->count();
        $thisMonthTenant = User::where('role', 'tenant')->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->count();

        $totalSubscribed = PlanSubscribe::distinct('user_id')->count('user_id');
        $lastMonthSubscribed = PlanSubscribe::whereMonth('created_at', $lastMonth)->whereYear('created_at', $lastMonthYear)->distinct('user_id')->count('user_id');
        $thisMonthSubscribed = PlanSubscribe::whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->distinct('user_id')->count('user_id');

        // Compare and set arrow
        $landlordArrow = getArrow($lastMonthLandlord, $thisMonthLandlord);
        $tenantArrow = getArrow($lastMonthTenant, $thisMonthTenant);
        $subscribedArrow = getArrow($lastMonthSubscribed, $thisMonthSubscribed);

        $data = [
            'total_landlord' => $totalLandlord,
            'last_month_total_landlord' => $lastMonthLandlord,
            'landlord_arrow' => $landlordArrow,

            'total_tenant' => $totalTenant,
            'last_month_total_tenant' => $lastMonthTenant,
            'tenant_arrow' => $tenantArrow,

            'total_subscribed' => $totalSubscribed,
            'last_month_total_subscribed' => $lastMonthSubscribed,
            'subscribed_arrow' => $subscribedArrow,
        ];

        return response()->json($data);
    }

    public function property_chart(Request $request)
    {
        $year = $request->get('year', date('Y'));

        $total_property = Property::whereYear('created_at', $year)->count();
        $pending = Property::whereYear('created_at', $year)->where('status', 0)->count();
        $approved = Property::whereYear('created_at', $year)->where('status', 1)->count();
        $rejected = Property::whereYear('created_at', $year)->where('status', 3)->count();

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

        $incomeData = Income::whereNull('landlord_id')
            ->whereYear('income_date', $year)
            ->selectRaw('MONTH(income_date) as month, SUM(amount) as total_income')
            ->groupBy('month')
            ->pluck('total_income', 'month')
            ->toArray();

        $expenseData = Expense::whereNull('landlord_id')
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
