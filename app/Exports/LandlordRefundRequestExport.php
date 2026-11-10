<?php

namespace App\Exports;

use App\Models\RefundRequest;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LandlordRefundRequestExport implements FromView
{
    public function view(): View
    {
        $refund_requests = RefundRequest::with(['property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'tenant:id,name', 'deposit:id,deposit_amount,utility_advance,rent_advance,total_amount'])->latest()->get();

        return view('landlord.refund-request.excel-csv', [
            'refund_requests' => $refund_requests
        ]);
    }
}
