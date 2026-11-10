<?php

namespace App\Exports;

use App\Models\Expense;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class LandlordExpensesExport implements FromView
{
    public function view(): View
    {
        $expenses = Expense::with(['category:id,name','landlord:id,name'])->where('landlord_id', auth()->id())->latest()->get();
        return view('landlord.expenses.excel-csv', [
            'expenses' => $expenses
        ]);
    }
}
