<?php

namespace App\Exports;

use App\Models\RentPayment;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LandlordRentPaymentExport implements FromView
{
    public function view(): View
    {
        $landlord_id = auth()->id();
        $rent_payments = RentPayment::with(['rent:id,tenant_id,property_id', 'rent.tenant:id,name', 'rent.property:id,puid', 'rent.property.propertyDetail:id,property_id,property_info'])
            ->whereHas('rent', function ($query) use ($landlord_id) {
                $query->where('landlord_id', $landlord_id);
            })
            ->latest()->get();

        return view('landlord.rent-payments.excel-csv', [
            'rent_payments' => $rent_payments
        ]);
    }
}
