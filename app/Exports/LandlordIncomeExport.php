<?php

namespace App\Exports;

use App\Models\Income;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class LandlordIncomeExport implements FromView
{
    public function view(): View
    {
        $incomes = Income::with(['category:id,name','landlord:id,name'])->where('landlord_id', auth()->id())->latest()->get();
        return view('landlord.incomes.excel-csv', [
            'incomes' => $incomes
        ]);
    }
}
