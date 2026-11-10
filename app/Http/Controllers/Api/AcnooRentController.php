<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Rent;
use App\Models\Unit;
use App\Models\Option;
use App\Models\Property;
use App\Helpers\HasUploader;
use Illuminate\Http\Request;
use App\Models\SecurityDeposit;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AcnooRentController extends Controller
{
    use HasUploader;

    public function index(Request $request)
    {
        $query = Rent::select('id', 'monthly_rent', 'utility_bill', 'total_amount', 'start_date', 'end_date', 'status', 'landlord_id', 'tenant_id', 'property_id', 'unit_id')
            ->with('tenant:id,name,unique_id', 'landlord:id,name', 'property:id,category_id,address_info,rent_info', 'property.propertyDetail:property_id,property_info', 'unit:id,unit_name,min_rental_value,min_rental_unit')
            ->when(auth()->user()->role == 'landlord' || request('landlord_id'), function ($q) {
                $q->where('landlord_id', request('landlord_id') ?? auth()->id());
            })
            ->when(auth()->user()->role == 'tenant', function ($q) {
                $q->where('tenant_id', auth()->id());
            })
            ->when(request('status'), function ($q) {
                $q->where('status', request('status'));
            })
            ->when(request('status') == 'expired', function ($q) {
                $q->whereDate('start_date', '<=', Carbon::today())
                    ->whereDate('end_date', '>=', Carbon::today());
            })
            ->latest();
        if ($request->has('no_paginate') && $request->no_paginate == true) {
            $rents = Rent::select('id', 'tenant_id', 'property_id', 'status')->with('property:id', 'property.propertyDetail:property_id,property_info', 'security_deposit:rent_id,id,invoice_no')
                ->when(auth()->user()->role == 'tenant', function ($q) {
                    $q->where('tenant_id', auth()->id());
                })
                ->when(request('status'), function ($q) {
                    $q->where('status', request('status'));
                })
                ->get();
            $responseData = [
                'data' => $rents,
            ];
        } else {
            $rents = $query->paginate($request->per_page ?? 10);
            $responseData = $rents;
        }

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $responseData
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:users,id',
            'property_id' => 'required|exists:properties,id',
            'unit_id' => 'nullable|exists:units,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'landlord_agreement' => 'required|file',
        ]);

        $property = Property::findOrFail($request->property_id);

        //rental period validate
        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $rentalDays = $start->diffInDays($end);


        if ($request->unit_id) {
            $unit = Unit::where('property_id', $property->id)
                        ->findOrFail($request->unit_id);
            $minValue = $unit->min_rental_value ?? null;
            $minUnit  = $unit->min_rental_unit ?? null;

            $deposit_amount  = $unit->security_deposit ?? 0;
            $rent_advance    = $unit->monthly_rent ?? 0;
            $utility_advance = $unit->utility_deposit ?? 0;
        } else {
            $minValue = $property->rent_info['min_rental_value'] ?? null;
            $minUnit  = $property->rent_info['min_rental_unit'] ?? null;

            $deposit_amount  = $property->security_deposit ?? 0;
            $rent_advance    = $property->rent_info['monthly_rent'] ?? 0;
            $utility_advance = $property->utility_deposit ?? 0;
        }

        if ($minValue && $minUnit) {
            $minDays = match($minUnit) {
                'days'   => $minValue,
                'weeks'  => $minValue * 7,
                'months' => $minValue * 30,
                'years'  => $minValue * 365,
                default  => 0
            };

            if ($rentalDays < $minDays) {
                return response()->json([
                    'message' => "The rental period must be at least {$minValue} {$minUnit}."
                ], 422);
            }
        }

        $chargeOption = Option::where('key', 'service-charge')->first();
        $chargeData = $chargeOption->value;
        $amount = $chargeData['amount'] ?? 0;
        $type = $chargeData['type'] ?? 'fixed';


        // Rent total amount = rent + utility
        $rent_total_amount = $rent_advance + $utility_advance;
        //Security total amount
        $security_total_amount = $rent_advance + $utility_advance + $deposit_amount;

        // Security deposit + rent + utility for landlord balance
        $landlord_balance = $deposit_amount + $rent_advance + $utility_advance;

        $calculated_charge = ($type === 'percentage') ? ($security_total_amount * $amount) / 100 : $amount;
        $final_total_amount = $security_total_amount;

        DB::beginTransaction();
        try {
            $rent = Rent::create($request->except('landlord_agreement') + [
                'landlord_id' => auth()->id(),
                'utility_bill' => $utility_advance,
                'monthly_rent' => $rent_advance,
                'total_amount' => $rent_total_amount,
                'landlord_agreement' => $request->landlord_agreement ? $this->upload($request, 'landlord_agreement') : NULL,
            ]);

            SecurityDeposit::create([
                'tenant_id' => $request->tenant_id,
                'landlord_id' => auth()->id(),
                'rent_id' => $rent->id,
                'property_id' => $request->property_id,
                'unit_id' => $request->unit_id,
                'deposit_amount' => $deposit_amount,
                'rent_advance' => $rent_advance,
                'utility_advance' => $utility_advance,
                'total_amount' => $final_total_amount,
                'landlord_balance' => $landlord_balance - $calculated_charge,
                'charge' => $calculated_charge,
                'status' => 'unpaid',
            ]);

            DB::commit();

            sendNotification($rent->id, notify_users([$rent->tenant->id, $rent->landlord->id]), landlord_url: route('landlord.rents.index', ['id' => $rent->id]),  admin_msg: __('Landlord create a new rent invitation'), landlord_msg: __('A new rent invitation has been sent to the tenant. Waiting for approval.'), tenant_msg: __('You have received a new rent invitation. Please review and approve.'));

            return response()->json([
                'message' => 'Data save successfully',
                'data' => $rent
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    public function show(string $id)
    {
        $data = Rent::with([
            'tenant:id,unique_id,name,phone,email',
            'landlord:id,unique_id,name,phone,email',
            'property.category:id,name',
            'property.propertyDetail',
            'unit',
            'security_deposit:id,rent_id,status',
            'rent_payments' => function ($query) {
                $query->select('rent_id', 'payment_status', 'late_fee')
                    ->selectRaw('SUM(CASE WHEN payment_status = "paid" THEN subtotal_amount ELSE 0 END) as total_paid_rent')
                    ->selectRaw('SUM(CASE WHEN payment_status = "paid" AND MONTH(payment_date) = MONTH(CURRENT_DATE()) AND YEAR(payment_date) = YEAR(CURRENT_DATE()) THEN subtotal_amount ELSE 0 END) as this_month_payment')
                    ->selectRaw('SUM(CASE WHEN payment_status = "unpaid" THEN subtotal_amount ELSE 0 END) as due_rent')
                    ->selectRaw('CASE WHEN SUM(CASE WHEN payment_status = "paid" AND MONTH(payment_date) = MONTH(CURRENT_DATE()) AND YEAR(payment_date) = YEAR(CURRENT_DATE()) THEN subtotal_amount ELSE 0 END) > 0
                                THEN "paid"
                                ELSE "unpaid"
                            END as this_month_payment_status')
                    ->groupBy('rent_id', 'payment_status', 'late_fee');
            },
        ])->findOrFail($id);

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $data
        ]);
    }


    public function tenant_agreement(Request $request, string $id)
    {
        $rent = Rent::findOrFail($id);

        $rent->update([
            'tenant_agreement' => $request->tenant_agreement ? $this->upload($request, 'tenant_agreement') : NULL
        ]);

        sendNotification($rent->id, notify_users([$rent->tenant->id, $rent->landlord->id]), landlord_url: route('landlord.rents.index', ['id' => $rent->id]),  landlord_msg: __('The tenant has submitted the signed rent agreement. Please review it.'), tenant_msg: __('Your rent agreement has been submitted successfully. Awaiting landlord’s review.'));

        return response()->json([
            'message' => 'Agreement sent successfully',
            'data' => $rent
        ]);
    }

    public function tenant_accept(string $id)
    {
        $rent_accept = Rent::findOrFail($id);

        if ($rent_accept) {
            $rent_accept->update([
                'status' => 'processing'
            ]);

            sendNotification($rent_accept->id, notify_users([$rent_accept->tenant->id, $rent_accept->landlord->id]), landlord_url: route('landlord.rents.index', ['id' => $rent_accept->id]), landlord_msg: __('The tenant has accepted the rent invitation.'), tenant_msg: __('You have successfully accepted the rent invitation.'));

            return response()->json([
                'message' => 'Rent accepted successfully',
                'status' => $rent_accept
            ]);
        }
    }

    public function approved(string $id)
    {
        $rent_approved = Rent::findOrFail($id);

        $security_deposit = SecurityDeposit::where('rent_id', $rent_approved->id)->first();

        if ($security_deposit->status == 'paid') {
            $rent_approved->update([
                'status' => 'active',
            ]);

            sendNotification($rent_approved->id, notify_users([$rent_approved->tenant->id, $rent_approved->landlord->id]), landlord_url: route('landlord.rents.index', ['id' => $rent_approved->id]), landlord_msg: __('The rent agreement has been approved and is now active.'), tenant_msg: __('Your rent agreement has been approved and is now active. Welcome!'));

            return response()->json([
                'message' => 'Rent approved successfully',
                'status' =>  $rent_approved
            ]);
        } else {
            return response()->json(['message' => 'Rent not found'], 404);
        }
    }

    public function tenant_cancelled(Request $request, string $id)
    {
        $request->validate([
            'title' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $rent_cancelled = Rent::findOrFail($id);

        if ($rent_cancelled->status == 'active') {

            $rent_cancelled->update([
                'cancel_info' => [
                    'title' => $request->title,
                    'reason' => $request->reason,
                    'prev_end_date' => $rent_cancelled->end_date,
                ],
                'status' => 'cancelled',
            ]);

            sendNotification($rent_cancelled->id, notify_users([$rent_cancelled->tenant->id, $rent_cancelled->landlord->id]), landlord_url: route('landlord.rents.index', ['id' => $rent_cancelled->id]), landlord_msg: __('The tenant has cancelled the rent agreement.'), tenant_msg: __('You have successfully cancelled your rent agreement.'));

            return response()->json([
                'message' => 'Rent cancelled successfully',
                'data' =>  $rent_cancelled
            ]);
        } else {
            return response()->json(['message' => 'Rent not found'], 404);
        }
    }
}
