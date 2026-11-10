<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Rent;
use App\Models\User;
use App\Models\Property;
use App\Models\Withdraw;
use App\Models\Application;
use App\Models\Maintenance;
use App\Models\RentPayment;
use App\Models\Income;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AcnooLandlordDashboardController extends Controller
{

    public function index(Request $request)
    {
        $form_date = $request->form_date;
        $to_date = $request->to_date;

        $data['current_balance'] = auth()->user()->balance;

        $DateFilter = function ($query) use ($form_date, $to_date) {
            if ($form_date && $to_date) {
                $query->whereDate('created_at', '>=', $form_date)
                    ->whereDate('created_at', '<=', $to_date);
            }
            return $query;
        };

        $data['total_withdraw'] = $DateFilter(Withdraw::where('user_id', auth()->id())->where('status', 'approved'))->sum('amount');
        $data['total_properties'] = $DateFilter(Property::where('landlord_id', auth()->id()))->count();
        $data['total_tenant'] = $DateFilter(User::where('landlord_id', auth()->id()))->count();

        $rent = Rent::where('landlord_id', auth()->id())->get('id');
        $data['total_rent_paid'] = $DateFilter(RentPayment::whereIn('rent_id', $rent)
            ->where('payment_status', 'paid'))
            ->sum('total_amount');
        $data['total_rent_due'] = $DateFilter(RentPayment::whereIn('rent_id', $rent)
            ->where('payment_status', 'unpaid'))
            ->sum('total_amount');
        $property = Property::where('landlord_id', auth()->id())->get('id');
        $data['total_application'] = $DateFilter(Application::whereIn('property_id', $property))
            ->count();
        $data['pending_application'] = $DateFilter(Application::whereIn('property_id', $property)->where('status', 0))
            ->count();
        $data['processing_application'] = $DateFilter(Application::whereIn('property_id', $property)->where('status', 1))
            ->count();
        $data['approve_application'] = $DateFilter(Application::whereIn('property_id', $property)->where('status', 2))
            ->count();
        $data['reject_application'] = $DateFilter(Application::whereIn('property_id', $property)->where('status', 3))
            ->count();
        $data['pending_maintenance'] = $DateFilter(Maintenance::whereIn('property_id', $property)->where('status', 'pending'))
            ->count();
        $data['processing_maintenance'] = $DateFilter(Maintenance::whereIn('property_id', $property)->where('status', 'processing'))
            ->count();
        $data['maintenance_cost'] = $DateFilter(Maintenance::whereIn('property_id', $property))
            ->sum('total_costing');

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $data,
        ]);
    }

    public function property_chart(Request $request)
    {
        $form_date = $request->form_date;
        $to_date = $request->to_date;

        $DateFilter = function ($query) use ($form_date, $to_date) {
            if ($form_date && $to_date) {
                $query->whereDate('created_at', '>=', $form_date)
                    ->whereDate('created_at', '<=', $to_date);
            }
            return $query;
        };

        $propertiesQuery = Property::where('landlord_id', auth()->id());
        $properties = $DateFilter($propertiesQuery)->get();

        $totalProperties = $properties->count();

        $occupied = $properties->filter(function ($property) {
            return $property->rent()->where('status', 'active')->exists();
        })->count();

        $maintenance = $properties->filter(function ($property) {
            return $property->maintenances()->exists();
        })->count();

        $vacant = $totalProperties - $occupied - $maintenance;

        $data = [
            'total_properties' => $totalProperties,
            'occupied' => $occupied,
            'maintenance' => $maintenance,
            'vacant' => $vacant,
            'occupied_percentage' => $totalProperties > 0 ? round(($occupied / $totalProperties) * 100, 2) : 0,
            'vacant_percentage' => $totalProperties > 0 ? round(($vacant / $totalProperties) * 100, 2) : 0,
            'maintenance_percentage' => $totalProperties > 0 ? round(($maintenance / $totalProperties) * 100, 2) : 0,
        ];

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $data,
        ]);
    }


    public function account_chart(Request $request)
    {
        $currentDate = Carbon::now();

        switch ($request->input('duration')) {
            case 'weekly':
                $start = $currentDate->copy()->startOfWeek(Carbon::SATURDAY);
                $end = $currentDate->copy()->endOfWeek(Carbon::FRIDAY);
                $format = 'D';
                $period = $start->daysUntil($end);
                break;
            case 'monthly':
                $start = $currentDate->copy()->startOfMonth();
                $end = $currentDate->copy()->endOfMonth();
                $format = 'd';
                $period = $start->daysUntil($end);
                break;

            case 'yearly':
                $start = $currentDate->copy()->startOfYear();
                $end = $currentDate->copy()->endOfYear();
                $format = 'M';
                $period = $start->monthsUntil($end);
                break;

            default:
                return response()->json(['error' => 'Invalid duration'], 400);
        }

        $income_data = DB::table('incomes')
            ->select(DB::raw("DATE_FORMAT(income_date, '%Y-%m-%d') as date"), DB::raw("SUM(amount) as income_amount_data"))
            ->where('landlord_id', auth()->id())
            ->whereBetween('income_date', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $expense_data = DB::table('expenses')
            ->select(DB::raw("DATE_FORMAT(expense_date, '%Y-%m-%d') as date"), DB::raw("SUM(amount) as income_amount_data"))
            ->where('landlord_id', auth()->id())
            ->whereBetween('expense_date', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $income_amount = Income::where('landlord_id', auth()->id())
            ->whereBetween('income_date', [$start, $end])
            ->sum('amount');

        $expense_amount = Expense::where('landlord_id', auth()->id())
            ->whereBetween('expense_date', [$start, $end])
            ->sum('amount');

        $profit_loss = $income_amount - $expense_amount;

        // Determine Profit/Loss Percentage
        $percentage = 0;
        $status = 'equal';

        if ($profit_loss > 0) {
            $percentage = $income_amount > 0 ? round(($profit_loss / $income_amount) * 100, 2) : 0;
            $status = 'Profit';
        } elseif ($profit_loss < 0) {
            $percentage = $income_amount > 0 ? round((abs($profit_loss) / $income_amount) * 100, 2) : 0;
            $status = 'Loss';
        }

        $data = [
            'total_income' => (int) $income_amount,
            'total_expense' => (int) $expense_amount,
            'profit_loss' => (int) $profit_loss,
            'profit_loss_percentage' => $percentage,
            'status' => $status,
            'max_value' => (int) max($income_amount, $expense_amount),
            'min_value' => (int) min($income_amount, $expense_amount),
            'income_amount_data' => $this->formatData($period, $income_data, $format),
            'expense_amount_data' => $this->formatData($period, $expense_data, $format),
        ];

        return response()->json([
            'message' => __('Data fetched successfully.'),
            'data' => $data,
        ]);
    }

    private function formatData($period, $datas, $format)
    {
        $rows = [];
        foreach ($period as $date) {
            if (request('duration') == 'yearly') {
                $key = $date->format($format);
                $dateKey = $date->format('Y-m'); // For lookup purposes
                $amount = $datas->filter(function ($value, $key) use ($dateKey) {
                    return strpos($value->date, $dateKey) === 0;
                })->sum('income_amount_data' ?? 'expense_amount_data');
            } else {
                $key = $date->format($format);
                $amount = $datas->get($date->format('Y-m-d'))?->income_amount_data ??
                    $datas->get($date->format('Y-m-d'))?->expense_amount_data ?? 0;
            }

            $rows[] = [
                'date' => $key,
                'amount' => $amount,
            ];
        }

        return $rows;
    }
}
