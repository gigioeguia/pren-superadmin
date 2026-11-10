<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminTransactionExport;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class AcnooTransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:transactions-read')->only('index');
    }

    public function index()
    {
        $transactions = Transaction::with('paidBy:id,name', 'gateway:id,name')->latest()->paginate(10);
        return view('admin.transactions.index', compact('transactions'));
    }

    public function acnooFilter(Request $request)
    {
        $transactions = Transaction::with('paidBy:id,name', 'gateway:id,name')
            ->when(request('search'), function ($q) {
                $q->where('amount', 'like', '%' . request('search') . '%')
                    ->orWhere('uid', 'like', '%' . request('search') . '%')
                    ->orWhere('amount', 'like', '%' . request('search') . '%')
                    ->orWhere('payment_type', 'like', '%' . request('search') . '%');

                $q->orWhereHas('gateway', function ($q) {
                    $q->where('name', 'like', '%' . request('search') . '%');
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 20);

        if (request()->ajax()) {
            return response()->json([
                'data' => view('admin.transactions.datas', compact('transactions'))->render()
            ]);
        }
        return redirect(url()->previous());
    }

    public function exportExcel()
    {
        return Excel::download(new AdminTransactionExport, 'transactions.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new AdminTransactionExport, 'transactions.csv');
    }
}
