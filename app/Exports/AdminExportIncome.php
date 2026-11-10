<?php

namespace App\Exports;

use App\Models\Income;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AdminExportIncome implements FromView
{
    public function view(): View
    {
        $incomes = Income::with('category:id,name')->whereNull('landlord_id')->latest()->get();
        return view('admin.incomes.excel-csv', [
            'incomes' => $incomes
        ]);
    }
}
