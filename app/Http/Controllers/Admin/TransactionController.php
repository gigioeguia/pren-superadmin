<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminTransactionExport;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Traits\HasTableActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use HasTableActions;

    public function __construct()
    {
        $this->middleware('permission:transactions-read')->only('index', 'filter', 'exportExcel', 'exportCsv');
    }

    public function index()
    {
        $transactions = Transaction::with('paidBy:id,name', 'gateway:id,name')->latest()->paginate(10);

        return view('admin.transactions.index', compact('transactions'));
    }

    protected function model(): string
    {
        return Transaction::class;
    }

    protected function query(Request $request): Builder
    {
        return Transaction::with('paidBy:id,name', 'gateway:id,name')
            ->when($request->input('search'), function ($q) use ($request) {
                $q->where('amount', 'like', '%'.$request->input('search').'%')
                    ->orWhere('uid', 'like', '%'.$request->input('search').'%')
                    ->orWhere('amount', 'like', '%'.$request->input('search').'%')
                    ->orWhere('payment_type', 'like', '%'.$request->input('search').'%');

                $q->orWhereHas('gateway', function ($q) use ($request) {
                    $q->where('name', 'like', '%'.$request->input('search').'%');
                });
            })
            ->latest();
    }

    protected function view(): string
    {
        return 'admin.transactions.datas';
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
        return new AdminTransactionExport;
    }

    protected function excelFilename(): string
    {
        return 'transactions';
    }

    protected function csvFilename(): string
    {
        return 'transactions';
    }
}
