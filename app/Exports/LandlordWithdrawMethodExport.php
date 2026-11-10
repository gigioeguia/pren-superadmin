<?php

namespace App\Exports;

use App\Models\Withdraw;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LandlordWithdrawMethodExport implements FromView
{
    public function view(): View
    {
        $withdraws = Withdraw::with('account_info.withdraw_method:id,name,charge_type')->where('user_id', auth()->id())->latest()->get();

        return view('landlord.withdraw-request.excel-csv', [
            'withdraws' => $withdraws
        ]);
    }
}
