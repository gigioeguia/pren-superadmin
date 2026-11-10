<?php

namespace App\Http\Controllers\Api;

use App\Models\Rent;
use App\Models\User;
use App\Models\Property;
use App\Helpers\HasUploader;
use App\Models\RefundReason;
use Illuminate\Http\Request;
use App\Models\RefundRequest;
use App\Models\SecurityDeposit;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AcnooRefundRequestController extends Controller
{
    use HasUploader;

    public function index()
    {
        $refund_requests = RefundRequest::with(['property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'landlord:id,name', 'tenant:id,name', 'deposit:id,invoice_no,deposit_amount,utility_advance,rent_advance,total_amount'])
            ->when(auth()->user()->role == 'tenant', function ($query) {
                $query->where('tenant_id', auth()->id());
            })
            ->when(auth()->user()->role == 'landlord', function ($query) {
                $query->where('landlord_id', auth()->id());
            })

            ->when(request('status'), function ($q) {
                $q->where('status', request('status'));
            })
            ->when(request('from_date') && request('to_date'), function ($q) {
                $q->whereDate('created_at', '>=', request('from_date'));
                $q->whereDate('created_at', '<=', request('to_date'));
            })

            ->latest()
            ->paginate($request->per_page ?? 10);


        return response()->json([
            'message' => 'Data fetched successfully.',
            'data' => $refund_requests
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

        sendNotification($property->id, notify_users([$refund_request->tenant->id, $refund_request->landlord->id]), landlord_url: route('landlord.refund-request.index', ['id' => $refund_request->id]),  landlord_msg: __('You have a refund request'), tenant_msg: __('Refund request has been successfully send'));

        return response()->json([
            'message' => 'Refund request has been submitted successfully.',
            'data' => $refund_request
        ]);
    }

    public function show(string $id)
    {
        $refund = RefundRequest::with(['property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'landlord:id,name,phone,email', 'landlord.user_details:user_id,address_info', 'tenant:id,name,phone,email', 'tenant.user_details:user_id,address_info', 'deposit:id,deposit_amount,utility_advance,rent_advance,total_amount'])->findOrFail($id);

        $errors = session()->get('errors', new \Illuminate\Support\ViewErrorBag);
        $html = view('api-invoice.refund-invoice', compact('refund', 'errors'))->render();

        return response()->json([
            'message' => 'Data fetched successfully.',
            'data' => [
                'id' => $refund->id,
                'total_amount' => $refund->amount
            ],
            'html' => $html
        ]);
    }

    public function upload_receipt(Request $request, string $id)
    {
        $request->validate([
            'attachment' => 'required|file',
        ]);

        $refund_request = RefundRequest::findOrFail($id);

        $refund_request->update([
            'attachment' => $request->attachment ? $this->upload($request, 'attachment') : NULL
        ]);

        return response()->json([
            'message' => 'Receipt uploaded successfully.',
            'data' => $refund_request
        ]);
    }

    public function approved(string $id)
    {
        $refund_request = RefundRequest::findOrFail($id);
        $security_deposit = SecurityDeposit::where('id', $refund_request->deposit_id)->first();
        $rent = Rent::where('property_id', $refund_request->property_id)->first();
        $landlord = User::findOrfail($refund_request->landlord_id);

        DB::beginTransaction();
        try {
            $refund_request->update([
                'status' => 'paid'
            ]);

            $security_deposit->update([
                'status' => 'refund'
            ]);

            $rent->update([
                'status' => 'cancelled',
                'cancel_info' => [
                    'title' => 'Refund issue',
                    'reason' => 'Rent cancelled for refund',
                    'prev_end_date' => $rent->end_date,
                ],
            ]);

            $landlord->decrement('balance', $refund_request->amount);

            DB::commit();

            sendNotification($refund_request->id, notify_users([$refund_request->tenant->id, $refund_request->landlord->id]), landlord_url: route('landlord.refund-request.index', ['id' => $refund_request->id]), landlord_msg: __('Tenant has approved the refund request.Refund marked as a paid'), tenant_msg: __('Your refund has been approved'));

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => $th->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'Refund request has been approved successfully.',
            'data' => $refund_request
        ]);
    }
}
