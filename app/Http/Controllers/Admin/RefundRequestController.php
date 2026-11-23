<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Channel;
use App\Exports\AdminRefundRequestReportExport;
use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use App\Services\RefundRequestService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RefundRequestController extends Controller
{
    public function __construct(protected RefundRequestService $service)
    {
        $this->middleware('permission:reports-read')->only('index', 'show', 'filter', 'exportExcel', 'exportCsv');
    }

    public function index()
    {
        $refund_requests = $this->service->listForChannel(Channel::Admin, auth()->user(), ['per_page' => 10]);

        return view('admin.reports.refund-request.index', compact('refund_requests'));
    }

    public function show(string $id)
    {
        $refund = RefundRequest::with([
            'property:id,puid',
            'property.propertyDetail:id,property_id,property_info',
            'landlord:id,name,phone,email',
            'landlord.user_details:user_id,address_info',
            'tenant:id,name,phone,email',
            'tenant.user_details:user_id,address_info',
            'deposit:id,deposit_amount,utility_advance,rent_advance,total_amount',
        ])->findOrFail($id);

        return view('landlord.refund-request.view', compact('refund'));
    }

    public function filter(Request $request)
    {
        $refund_requests = $this->service->listForChannel(Channel::Admin, auth()->user(), array_merge($request->all(), ['per_page' => $request->per_page ?? 20]));

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.reports.refund-request.datas', ['refund_requests' => $refund_requests])->render(),
            ]);
        }

        return redirect(url()->previous());
    }

    public function exportExcel()
    {
        return Excel::download(new AdminRefundRequestReportExport, 'refund-request-reports.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new AdminRefundRequestReportExport, 'refund-request-reports.csv');
    }
}
