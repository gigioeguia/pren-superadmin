<?php

namespace App\Http\Controllers\Landlord;

use App\Exports\LandlordRefundRequestExport;
use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AcnooRefundRequestController extends Controller
{
    public function index()
    {
        $refund_requests = RefundRequest::with(['property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'tenant:id,name', 'deposit:id,deposit_amount,utility_advance,rent_advance,total_amount'])->where('landlord_id', auth()->id())->latest()->paginate(10);

        return view('landlord.refund-request.index', compact('refund_requests'));
    }

    public function acnooFilter(Request $request)
    {
        $search = $request->input('search');

        $refund_requests = RefundRequest::with(['property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'tenant:id,name', 'deposit:id,deposit_amount,utility_advance,rent_advance,total_amount'])->where('landlord_id', auth()->id())->when($search, function ($q) use ($search) {
            $q->where(function ($q) use ($search) {
                $q->where('amount', 'like', '%'.$search.'%')
                    ->orWhereHas('tenant', function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('property', function ($q) use ($search) {
                        $q->where('puid', 'like', '%'.$search.'%');
                    })

                    ->orWhereHas('property.propertyDetail', function ($query) use ($search) {
                        $query->where('property_info->property_title', 'like', '%'.$search.'%');
                    });
            });
        })
            ->latest()
            ->paginate($request->per_page ?? 10);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.refund-request.datas', compact('refund_requests'))->render(),
            ]);
        }

        return redirect(url()->previous());
    }

    public function show(string $id)
    {

        $refund = RefundRequest::with(['property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'landlord:id,name,phone,email', 'landlord.user_details:user_id,address_info', 'tenant:id,name,phone,email', 'tenant.user_details:user_id,address_info', 'deposit:id,deposit_amount,utility_advance,rent_advance,total_amount'])->findOrFail($id);

        return view('landlord.refund-request.view', compact('refund'));
    }

    public function exportExcel()
    {
        return Excel::download(new LandlordRefundRequestExport, 'refund-request.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new LandlordRefundRequestExport, 'refund-request.csv');
    }
}
