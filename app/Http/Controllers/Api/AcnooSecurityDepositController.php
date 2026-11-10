<?php

namespace App\Http\Controllers\Api;

use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\Gateway;
use App\Models\RentPayment;
use App\Helpers\HasUploader;
use Illuminate\Http\Request;
use App\Models\SecurityDeposit;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AcnooSecurityDepositController extends Controller
{
    use HasUploader;

    public function index(Request $request)
    {
        $security_deposit = SecurityDeposit::with(['property:id,puid', 'property.propertyDetail:property_id,property_info', 'landlord:id,name,phone', 'tenant:id,name'])
            ->when(auth()->user()->role == 'landlord', function ($q) {
                $q->where('landlord_id', auth()->id());
            })
            ->when(auth()->user()->role == 'tenant', function ($q) {
                $q->where('tenant_id', auth()->id());
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
            'message' => __('Data fetched successfully.'),
            'data' => $security_deposit
        ]);
    }

    public function show(string $id)
    {
        $security_deposit = SecurityDeposit::with(['property:id,security_deposit,utility_deposit,rent_info', 'property.propertyDetail:property_id,property_info', 'landlord:id,name,phone,email', 'landlord.user_details:user_id,address_info', 'tenant:id,name,phone,email', 'tenant.user_details:user_id,address_info'])->findOrFail($id);

        $errors = session()->get('errors', new \Illuminate\Support\ViewErrorBag);
        $html = view('api-invoice.security-deposit', compact('security_deposit', 'errors'))->render();

        return response()->json([
            'message' => 'Data fetched successfully.',
            'data' => [
                'id' => $security_deposit->id,
                'total_amount' => $security_deposit->total_amount
            ],
            'html' => $html
        ]);
    }

    public function offline_payment(Request $request, string $gateway_id, string $id)
    {
        $gateway = Gateway::findOrFail($gateway_id);

        if ($gateway->is_manual == 1) {
            $request->validate([
                'payment_receipt' => 'required|file',
                'payment_note' => 'nullable|string',
            ]);

            DB::beginTransaction();
            try {
                $payment_data = [
                    'payment_note' => $request->payment_note,
                    'payment_receipt' => $request->payment_receipt ? $this->upload($request, 'payment_receipt') : null,
                ];

                if ($request->payment_type == 'subscription') {
                    $plan = Plan::findOrFail($id);
                    $duration = 0;
                    if ($plan->duration_type == 'month') {
                        $duration = $plan->duration * 30;
                    } elseif ($plan->duration_type == 'year') {
                        $duration = $plan->duration * 365;
                    } elseif ($plan->duration_type == 'day') {
                        $duration = $plan->duration;
                    }

                    PlanSubscribe::create([
                        'user_id' => auth()->id(),
                        'plan_id' => $plan->id,
                        'plan_name' => $plan->subscriptionName,
                        'number_of_property' => $plan->number_of_property,
                        'number_of_tenant' => $plan->number_of_tenant,
                        'number_of_labor' => $plan->number_of_labor,
                        'gateway_id' => $gateway_id,
                        'price' => $plan->offerPrice ?? $plan->subscriptionPrice,
                        'duration' => $duration,
                        'payment_status' => 'unpaid',
                        'notes' => [
                            'manual_data' => $request->payment_note,
                            'attachment' => $request->payment_receipt ? $this->upload($request, 'payment_receipt') : null,
                        ],
                    ]);

                    DB::commit();

                    session()->forget('user_id');
                    return response()->json([
                        'message' => 'New subscription purchased requested.',
                    ]);
                }

                if ($request->payment_type == 'security_deposit') {

                    $security_deposit = SecurityDeposit::findOrFail($id);
                    $security_deposit->update([
                        'payment_date' => today(),
                        'status' => 'pending',
                        'gateway_id' => $gateway->id,
                        'payment_data' => $payment_data

                    ]);
                } elseif ($request->payment_type == 'rent_payment') {

                    $rent_payment = RentPayment::findOrFail($id);
                    $rent_payment->update([
                        'payment_date' => today(),
                        'payment_status' => 'pending',
                        'gateway_id' => $gateway->id,
                        'payment_data' => $payment_data

                    ]);
                }

                DB::commit();

                return response()->json([
                    'message' => 'Payment sent successfully',
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'message' => 'Payment failed',
                ], 500);
            }
        }

        return response()->json([
            'message' => 'Invalid payment gateway',
        ], 400);
    }
}
