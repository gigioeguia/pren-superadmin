<?php

namespace App\Exports;

use App\Models\Withdraw;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AdminWithdrawRequestExport implements FromView
{
    public function view(): View
    {
        $withdraws = Withdraw::with(['account_info:id,infos', 'user:id,name'])->latest()->get();
        return view('admin.withdrawals.excel-csv', [
            'withdraws' => $withdraws
        ]);
    }
}
