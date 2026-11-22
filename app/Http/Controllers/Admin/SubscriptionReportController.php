<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminSubscriptionReportExport;
use App\Http\Controllers\Controller;
use App\Models\PlanSubscribe;
use App\Traits\HasTableActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SubscriptionReportController extends Controller
{
    use HasTableActions;

    public function __construct()
    {
        $this->middleware('permission:subscriptions-read')->only('index', 'filter', 'exportExcel', 'exportCsv');
    }

    public function index()
    {
        $subscriptions = PlanSubscribe::with(['user:id,name,will_expire', 'plan:id,subscriptionName', 'gateway:id,name'])->latest()->paginate(10);

        return view('admin.reports.subscriptions.index', compact('subscriptions'));
    }

    protected function model(): string
    {
        return PlanSubscribe::class;
    }

    protected function query(Request $request): Builder
    {
        $subscriptionQuery = PlanSubscribe::with(['user:id,name', 'plan:id,subscriptionName', 'gateway:id,name']);

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $startDate = Carbon::parse($request->from_date)->format('Y-m-d');
            $endDate = Carbon::parse($request->to_date)->format('Y-m-d');

            $subscriptionQuery->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        }

        if ($request->filled('search')) {
            $subscriptionQuery->where(function ($q) use ($request) {
                $q->where('payment_status', 'like', '%'.$request->search.'%')
                    ->orWhereHas('user', function ($q) use ($request) {
                        $q->where('name', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('plan', function ($q) use ($request) {
                        $q->where('subscriptionName', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('gateway', function ($q) use ($request) {
                        $q->where('name', 'like', '%'.$request->search.'%');
                    });
            });
        }

        return $subscriptionQuery->latest();
    }

    protected function view(): string
    {
        return 'admin.reports.subscriptions.datas';
    }

    protected function viewData(LengthAwarePaginator $rows): array
    {
        return ['subscriptions' => $rows];
    }

    protected function export(Request $request): object
    {
        return new AdminSubscriptionReportExport;
    }

    protected function excelFilename(): string
    {
        return 'subscription-reports';
    }

    protected function csvFilename(): string
    {
        return 'subscription-reports';
    }
}
