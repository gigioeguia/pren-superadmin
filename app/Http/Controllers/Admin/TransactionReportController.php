<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminTransactionReportExport;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Traits\HasTableActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TransactionReportController extends Controller
{
    use HasTableActions;

    public function __construct()
    {
        $this->middleware('permission:transactions-read')->only('index', 'filter', 'exportExcel', 'exportCsv');
    }

    public function index()
    {
        $transactions = Transaction::with('gateway:id,name')->latest()->paginate(10);

        return view('admin.reports.transactions.index', compact('transactions'));
    }

    protected function model(): string
    {
        return Transaction::class;
    }

    protected function query(Request $request): Builder
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
                $q->where('amount', 'like', '%'.$request->search.'%')
                    ->orWhere('uid', 'like', '%'.$request->search.'%')
                    ->orWhere('payment_type', 'like', '%'.$request->search.'%')
                    ->orWhereHas('gateway', function ($q) use ($request) {
                        $q->where('name', 'like', '%'.$request->search.'%');
                    });
            });
        }

        return $transactionQuery->latest();
    }

    protected function view(): string
    {
        return 'admin.reports.transactions.datas';
    }

    protected function viewData(LengthAwarePaginator $rows): array
    {
        return ['transactions' => $rows];
    }

    protected function perPage(): int
    {
        return 20;
    }

    protected function export(Request $request): object
    {
        return new AdminTransactionReportExport;
    }

    protected function excelFilename(): string
    {
        return 'transaction-reports';
    }

    protected function csvFilename(): string
    {
        return 'transaction-reports';
    }
}
