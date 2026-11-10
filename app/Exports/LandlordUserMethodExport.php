<?php

namespace App\Exports;

use App\Models\User;
use App\Models\UserMethod;
use App\Models\WithdrawMethod;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LandlordUserMethodExport implements FromView
{
    public function view(): View
    {
        $withdraw_methods = UserMethod::with(['withdraw_method:id,name,charge,charge_type,meta'])->where('user_id', auth()->id())->get();
        $methods = WithdrawMethod::latest()->get();
        $user = User::where('id', auth()->user()->id)->first();

        return view('landlord.withdraw-method.excel-csv', [
            'withdraw_methods' => $withdraw_methods,
            'methods' => $methods,
            'user' => $user,
        ]);
    }
}
