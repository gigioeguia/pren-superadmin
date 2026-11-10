<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\PlanSubscribe;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdminSubscriptionReportExport;
use Illuminate\Support\Carbon;

class AcnooSubscriptionReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:subscriptions-read')->only('index');
    }

    public function index()
    {
        $subscriptions = PlanSubscribe::with(['user:id,name,will_expire', 'plan:id,subscriptionName', 'gateway:id,name'])->latest()->paginate(10);
        return view('admin.reports.subscriptions.index', compact('subscriptions'));
    }

    public function acnooFilter(Request $request)
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
                $q->where('payment_status', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('plan', function ($q) use ($request) {
                        $q->where('subscriptionName', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('gateway', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $perPage = $request->input('per_page', 10);
        $subscriptions = $subscriptionQuery->latest()->paginate($perPage);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.reports.subscriptions.datas', compact('subscriptions'))->render()
            ]);
        }

        return redirect(url()->previous());
    }

    public function exportExcel()
    {
        return Excel::download(new AdminSubscriptionReportExport, 'subscription-reports.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new AdminSubscriptionReportExport, 'subscription-reports.csv');
    }
}
