<?php

namespace App\Exports;

use App\Models\SecurityDeposit;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LandlordSecurityDepositExport implements FromView
{
    public function view(): View
    {
        $security_deposits = SecurityDeposit::with(['tenant:id,name', 'property:id', 'property.propertyDetail:property_id,property_info', 'gateway:id,name'])->latest()->get();
        return view('landlord.security-deposits.excel-csv', [
            'security_deposits' => $security_deposits
        ]);
    }
}
