<?php

namespace App\Exports;

use App\Models\RefundRequest;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AdminRefundRequestReportExport implements FromView
{
    public function view(): View
    {
        $refund_requests = RefundRequest::with(['property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'landlord:id,name', 'tenant:id,name', 'deposit:id,total_amount'])->latest()->get();

        return view('admin.reports.refund-request.excel-csv', [
            'refund_requests' => $refund_requests
        ]);
    }
}
