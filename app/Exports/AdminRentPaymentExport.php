<?php

namespace App\Exports;

use App\Models\RentPayment;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AdminRentPaymentExport implements FromView
{
    public function view(): View
    {
        $rent_payments = RentPayment::with(['rent:id,tenant_id,property_id', 'rent.tenant:id,name', 'rent.property:id,puid', 'rent.property.propertyDetail:id,property_id,property_info', 'gateway:id,name'])->latest()->get();

        return view('admin.rent-payment.excel-csv', [
            'rent_payments' => $rent_payments
        ]);
    }
}
