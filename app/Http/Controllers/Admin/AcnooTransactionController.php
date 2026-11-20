<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminTransactionExport;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AcnooTransactionController extends Controller
{
    use HasAcnooActions;

    public function __construct()
    {
        $this->middleware('permission:transactions-read')->only('index', 'acnooFilter', 'exportExcel', 'exportCsv');
    }

    public function index()
    {
        $transactions = Transaction::with('paidBy:id,name', 'gateway:id,name')->latest()->paginate(10);

        return view('admin.transactions.index', compact('transactions'));
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return Transaction::class;
    }

    protected function acnooQuery(Request $request): Builder
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

    protected function acnooView(): string
    {
        return 'admin.transactions.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['transactions' => $rows];
    }

    protected function acnooPerPage(): int
    {
        return 20;
    }

    protected function acnooExport(Request $request): object
    {
        return new AdminTransactionExport;
    }

    protected function acnooExcelFilename(): string
    {
        return 'transactions';
    }

    protected function acnooCsvFilename(): string
    {
        return 'transactions';
    }
}
