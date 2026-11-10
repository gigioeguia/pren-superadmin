<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminTransactionReportExport;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class AcnooTransactionReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:transactions-read')->only('index');
    }

    public function index()
    {
        $transactions = Transaction::with('gateway:id,name')->latest()->paginate(10);
        return view('admin.reports.transactions.index', compact('transactions'));
    }

    public function acnooFilter(Request $request)
    {
        $transactionQuery = Transaction::with('gateway:id,name');

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $startDate = Carbon::parse($request->from_date)->format('Y-m-d');
            $endDate = Carbon::parse($request->to_date)->format('Y-m-d');

            $transactionQuery->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        }

        if ($request->filled('search')) {
            $transactionQuery->where(function ($q) use ($request) {
                $q->where('amount', 'like', '%' . $request->search . '%')
                    ->orWhere('uid', 'like', '%' . $request->search . '%')
                    ->orWhere('payment_type', 'like', '%' . $request->search . '%')
                    ->orWhereHas('gateway', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $perPage = $request->input('per_page', 20);
        $transactions = $transactionQuery->latest()->paginate($perPage);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.reports.transactions.datas', compact('transactions'))->render()
            ]);
        }

        return redirect(url()->previous());
    }

    public function exportExcel()
    {
        return Excel::download(new AdminTransactionReportExport, 'transaction-reports.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new AdminTransactionReportExport, 'transaction-reports.csv');
    }
}
