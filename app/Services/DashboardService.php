<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Maintenance;
use App\Models\Property;
use App\Models\Rent;
use App\Models\RentPayment;
use App\Models\User;
use App\Models\Withdraw;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard API de landlord (spec controladores-residuales-fase5): lógica
 * movida verbatim de Api/LandlordDashboardController. El service recibe
 * $landlord explícito (no lee auth()); el controlador queda como capa de
 * transporte. Caracterización: tests/Feature/Dashboard.
 */
class DashboardService
{
    public function summary(User $landlord, ?string $fromDate = null, ?string $toDate = null): array
    {
        $data['current_balance'] = $landlord->balance;

        $data['total_withdraw'] = $this->dateFilter(Withdraw::where('user_id', $landlord->id)->where('status', 'approved'), $fromDate, $toDate)->sum('amount');
        $data['total_properties'] = $this->dateFilter(Property::where('landlord_id', $landlord->id), $fromDate, $toDate)->count();
        $data['total_tenant'] = $this->dateFilter(User::where('landlord_id', $landlord->id), $fromDate, $toDate)->count();

        $rent = Rent::where('landlord_id', $landlord->id)->get('id');
        $data['total_rent_paid'] = $this->dateFilter(RentPayment::whereIn('rent_id', $rent)
            ->where('payment_status', 'paid'), $fromDate, $toDate)->sum('total_amount');
        $data['total_rent_due'] = $this->dateFilter(RentPayment::whereIn('rent_id', $rent)
            ->where('payment_status', 'unpaid'), $fromDate, $toDate)->sum('total_amount');
        $property = Property::where('landlord_id', $landlord->id)->get('id');
        $data['total_application'] = $this->dateFilter(Application::whereIn('property_id', $property), $fromDate, $toDate)
            ->count();
        $data['pending_application'] = $this->dateFilter(Application::whereIn('property_id', $property)->where('status', 0), $fromDate, $toDate)
            ->count();
        $data['processing_application'] = $this->dateFilter(Application::whereIn('property_id', $property)->where('status', 1), $fromDate, $toDate)
            ->count();
        $data['approve_application'] = $this->dateFilter(Application::whereIn('property_id', $property)->where('status', 2), $fromDate, $toDate)
            ->count();
        $data['reject_application'] = $this->dateFilter(Application::whereIn('property_id', $property)->where('status', 3), $fromDate, $toDate)
            ->count();
        $data['pending_maintenance'] = $this->dateFilter(Maintenance::whereIn('property_id', $property)->where('status', 'pending'), $fromDate, $toDate)
            ->count();
        $data['processing_maintenance'] = $this->dateFilter(Maintenance::whereIn('property_id', $property)->where('status', 'processing'), $fromDate, $toDate)
            ->count();
        $data['maintenance_cost'] = $this->dateFilter(Maintenance::whereIn('property_id', $property), $fromDate, $toDate)
            ->sum('total_costing');

        return $data;
    }

    public function propertyChart(User $landlord, ?string $fromDate = null, ?string $toDate = null): array
    {
        $properties = $this->dateFilter(Property::where('landlord_id', $landlord->id), $fromDate, $toDate)->get();

        $totalProperties = $properties->count();

        $occupied = $properties->filter(function ($property) {
            return $property->rent()->where('status', 'active')->exists();
        })->count();

        $maintenance = $properties->filter(function ($property) {
            return $property->maintenances()->exists();
        })->count();

        $vacant = $totalProperties - $occupied - $maintenance;

        return [
            'total_properties' => $totalProperties,
            'occupied' => $occupied,
            'maintenance' => $maintenance,
            'vacant' => $vacant,
            'occupied_percentage' => $totalProperties > 0 ? round(($occupied / $totalProperties) * 100, 2) : 0,
            'vacant_percentage' => $totalProperties > 0 ? round(($vacant / $totalProperties) * 100, 2) : 0,
            'maintenance_percentage' => $totalProperties > 0 ? round(($maintenance / $totalProperties) * 100, 2) : 0,
        ];
    }

    public function accountChart(User $landlord, ?string $duration): array
    {
        $currentDate = Carbon::now();

        switch ($duration) {
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
                throw new \InvalidArgumentException('Invalid duration');
        }

        $income_data = DB::table('incomes')
            ->select(DB::raw("DATE_FORMAT(income_date, '%Y-%m-%d') as date"), DB::raw('SUM(amount) as income_amount_data'))
            ->where('landlord_id', $landlord->id)
            ->whereBetween('income_date', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $expense_data = DB::table('expenses')
            ->select(DB::raw("DATE_FORMAT(expense_date, '%Y-%m-%d') as date"), DB::raw('SUM(amount) as income_amount_data'))
            ->where('landlord_id', $landlord->id)
            ->whereBetween('expense_date', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $income_amount = Income::where('landlord_id', $landlord->id)
            ->whereBetween('income_date', [$start, $end])
            ->sum('amount');

        $expense_amount = Expense::where('landlord_id', $landlord->id)
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

        return [
            'total_income' => (int) $income_amount,
            'total_expense' => (int) $expense_amount,
            'profit_loss' => (int) $profit_loss,
            'profit_loss_percentage' => $percentage,
            'status' => $status,
            'max_value' => (int) max($income_amount, $expense_amount),
            'min_value' => (int) min($income_amount, $expense_amount),
            'income_amount_data' => $this->formatData((string) $duration, $period, $income_data, $format),
            'expense_amount_data' => $this->formatData((string) $duration, $period, $expense_data, $format),
        ];
    }

    private function dateFilter($query, ?string $fromDate, ?string $toDate)
    {
        if ($fromDate && $toDate) {
            $query->whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate);
        }

        return $query;
    }

    /**
     * Formatea las series del account-chart sobre el período pedido.
     * Quirk preexistente preservado: el sum() original usaba
     * 'income_amount_data' ?? 'expense_amount_data' (coalescencia sobre
     * constantes, siempre resolvía a la izquierda) — ambas consultas
     * alían la columna como income_amount_data.
     *
     * @param  Collection<(int|string), \stdClass>  $datas
     * @param  CarbonPeriod  $period
     * @return array<int, array{date: string, amount: mixed}>
     */
    private function formatData(string $duration, $period, $datas, string $format): array
    {
        $rows = [];
        foreach ($period as $date) {
            if ($duration === 'yearly') {
                $key = $date->format($format);
                $dateKey = $date->format('Y-m'); // For lookup purposes
                $amount = $datas->filter(function ($value, $key) use ($dateKey) {
                    return strpos((string) $value->date, $dateKey) === 0;
                })->sum('income_amount_data');
            } else {
                $key = $date->format($format);
                $row = $datas->get($date->format('Y-m-d'));
                // Lookup verbatim: fila ausente -> 0; presente -> income ?? expense ?? 0
                $amount = $row === null ? 0 : ($row->income_amount_data ?? $row->expense_amount_data ?? 0);
            }

            $rows[] = [
                'date' => $key,
                'amount' => $amount,
            ];
        }

        return $rows;
    }
}
