<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use App\Services\RefundRequestService;
use Illuminate\Http\Request;

class RefundRequestController extends Controller
{
    public function __construct(protected RefundRequestService $service) {}

    public function index()
    {
        $refund_requests = $this->service->listForChannel(Channel::Landlord, auth()->user(), ['per_page' => 10]);

        return view('landlord.refund-request.index', compact('refund_requests'));
    }

    public function show(string $id)
    {
        $refund = RefundRequest::with(['property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'landlord:id,name,phone,email', 'landlord.user_details:user_id,address_info', 'tenant:id,name,phone,email', 'tenant.user_details:user_id,address_info', 'deposit:id,deposit_amount,utility_advance,rent_advance,total_amount'])->findOrFail($id);

        return view('landlord.refund-request.view', compact('refund'));
    }

    public function filter(Request $request)
    {
        $refund_requests = $this->service->listForChannel(Channel::Landlord, auth()->user(), array_merge($request->all(), ['per_page' => $request->per_page ?? 20]));

        return view('landlord.refund-request.datas', ['refund_requests' => $refund_requests]);
    }
}
