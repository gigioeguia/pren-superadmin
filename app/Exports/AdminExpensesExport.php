<?php

namespace App\Exports;

use App\Models\Expense;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AdminExpensesExport implements FromView
{
    public function view(): View
    {
        $expenses = Expense::with('category:id,name')->whereNull('landlord_id')->latest()->get();
        return view('admin.expenses.excel-csv', [
            'expenses' => $expenses
        ]);
    }
}
