<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AdminTransactionReportExport implements FromView
{
    public function view(): View
    {
        $transactions = Transaction::latest()->get();
        return view('admin.reports.transactions.excel-csv', [
            'transactions' => $transactions
        ]);
    }
}
