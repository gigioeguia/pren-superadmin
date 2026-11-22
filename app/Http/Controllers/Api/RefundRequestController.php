<?php

namespace App\Http\Controllers\Api;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\RefundReason;
use App\Models\RefundRequest;
use App\Models\SecurityDeposit;
use App\Services\RefundRequestService;
use Illuminate\Http\Request;

class RefundRequestController extends Controller
{
    public function __construct(protected RefundRequestService $service) {}

    public function index(Request $request)
    {
        $refund_requests = $this->service->listForChannel(Channel::Api, $request->user(), $request->all());

        return response()->json([
            'message' => 'Data fetched successfully.',
            'data' => $refund_requests,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'property_id' => 'required|integer|exists:properties,id',
            'reason_id' => 'required|integer|exists:refund_reasons,id',
            'bank_info' => 'nullable|array',
        ]);

        $property = Property::findOrFail($request->property_id);
        $refund_reason = RefundReason::findOrFail($request->reason_id);
        $security_deposit = SecurityDeposit::where('property_id', $property->id)->first();

        $existing_refund_request = RefundRequest::where('deposit_id', $security_deposit->id)->exists();
        if ($existing_refund_request) {
            return response()->json([
                'message' => 'A refund request has already been submitted for this deposit.',
            ], 409);
        }

        $refund_request = RefundRequest::create([
            'deposit_id' => $security_deposit->id,
            'landlord_id' => $property->landlord_id,
            'tenant_id' => auth()->id(),
            'property_id' => $property->id,
            'reason_id' => $request->reason_id,
            'refund_reason' => $refund_reason->reason,
            'amount' => $security_deposit->total_amount,
            'bank_info' => $request->bank_info,
        ]);

        sendNotification($property->id, notify_users([$refund_request->tenant_id, $refund_request->landlord_id]), landlord_url: route('landlord.refund-request.index', ['id' => $refund_request->id]), landlord_msg: __('You have a refund request'), tenant_msg: __('Refund request has been successfully send'));

        return response()->json([
            'message' => 'Refund request has been submitted successfully.',
            'data' => $refund_request,
        ]);
    }

    public function show(string $id)
    {
        $refund = RefundRequest::with(['property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'landlord:id,name,phone,email', 'landlord.user_details:user_id,address_info', 'tenant:id,name,phone,email', 'tenant.user_details:user_id,address_info', 'deposit:id,deposit_amount,utility_advance,rent_advance,total_amount'])->findOrFail($id);

        return response()->json([
            'message' => 'Data fetched successfully.',
            'data' => $refund,
        ]);
    }

    public function upload_receipt(Request $request, string $id)
    {
        $request->validate([
            'receipt' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        $refund = RefundRequest::findOrFail($id);
        $refund->update([
            'attachment' => $request->file('receipt')->store('uploads/refunds', 'public'),
        ]);

        return response()->json([
            'message' => 'Receipt uploaded successfully.',
            'data' => $refund,
        ]);
    }

    public function approved(string $id)
    {
        $refund = RefundRequest::findOrFail($id);
        $refund = $this->service->approve($refund);

        return response()->json([
            'message' => 'Refund request approved.',
            'data' => $refund,
        ]);
    }
}
