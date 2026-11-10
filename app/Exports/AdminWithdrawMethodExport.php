<?php

namespace App\Exports;


use App\Models\WithdrawMethod;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AdminWithdrawMethodExport implements FromView
{
    public function view(): View
    {
        $withdrawMethods = WithdrawMethod::with('currency:id,name')->latest()->get();
        return view('admin.withdraw-method.excel-csv', [
            'withdrawMethods' => $withdrawMethods
        ]);
    }
}
