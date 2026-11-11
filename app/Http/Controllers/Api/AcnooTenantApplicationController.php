<?php

namespace App\Http\Controllers\Api;

use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Option;
use App\Models\Property;
use App\Models\Rent;
use App\Models\SecurityDeposit;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcnooTenantApplicationController extends Controller
{
    use HasUploader;

    public function index(Request $request)
    {
        $data = Application::with(['tenant:id,name,image,email', 'property:id,puid,address_info,cover_image,landlord_id', 'property.propertyDetail:property_id,property_info', 'property.landlord:id,name', 'unit'])
            ->when(request('status') !== null, function ($q) {
                $q->where('status', request('status'));
            })
            ->when(auth()->user()->role == 'landlord' || request('landlord_id'), function ($q) {
                $q->whereHas('property', function ($query) {
                    $query->where('landlord_id', auth()->id());
                });
            })
            ->when(auth()->user()->role == 'tenant', function ($q) {
                $q->where('tenant_id', auth()->id());
            })
            ->when($request->has('search') && $request->search !== null, function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('start_date', 'like', '%'.$search.'%')
                        ->orWhere('end_date', 'like', '%'.$search.'%')
                        ->orwhereHas('property.landlord', function ($landlord) {
                            $landlord->where('name', 'like', '%'.request('search').'%');
                        })
                        ->orwhereHas('tenant', function ($tenant) {
                            $tenant->where('name', 'like', '%'.request('search').'%')
                                ->orWhere('email', 'like', '%'.request('search').'%');
                        })
                        ->orwhereHas('property.propertyDetail', function ($propertyDetail) {
                            $propertyDetail->where('property_info', 'like', '%'.request('search').'%');
                        })
                        ->orwhereHas('property', function ($property) {
                            $property->where('address_info', 'like', '%'.request('search').'%')
                                ->orWhere('puid', 'like', '%'.request('search').'%');
                        });
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'message' => __('Data fetched successfully.'),
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'property_id' => 'required|integer',
            'document_file' => 'nullable',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'rejected_cause' => 'nullable|string',
        ]);

        $application = Application::where('property_id', $request->property_id)
            ->where('unit_id', $request->unit_id)
            ->where('tenant_id', auth()->user()->id)
            ->where('status', '!=', 3)
            ->first();

        if ($application) {
            return response()->json([
                'message' => 'You have already apply for this property',
            ], 409);
        }

        $property = Property::findOrFail($request->property_id);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);

        $rentalDays = $start->diffInDays($end);

        if ($request->unit_id) {
            $unit = Unit::where('property_id', $property->id)
                ->findOrFail($request->unit_id);
            $minValue = $unit->min_rental_value ?? null;
            $minUnit = $unit->min_rental_unit ?? null;
        } else {
            $minValue = $property->rent_info['min_rental_value'] ?? null;
            $minUnit = $property->rent_info['min_rental_unit'] ?? null;
        }

        if ($minValue && $minUnit) {
            $minDays = match ($minUnit) {
                'days' => $minValue,
                'weeks' => $minValue * 7,
                'months' => $minValue * 30,
                'years' => $minValue * 365,
                default => 0
            };

            if ($rentalDays < $minDays) {
                return response()->json([
                    'message' => "The rental period must be at least {$minValue} {$minUnit}.",
                ], 422);
            }
        }

        $data = Application::create($request->except('document_file', 'tenant_id') + [
            'document_file' => $request->document_file ? $this->upload($request, 'document_file') : null,
            'tenant_id' => auth()->user()->id,
            'unit_id' => $request->unit_id ?? null,
        ]);

        sendNotification($data->id, notify_users([$data->tenant->id, $data->property->landlord->id]), landlord_url: route('landlord.tenant-applications.index', ['id' => $data->id]), admin_msg: __('New Property Application has been created'), tenant_msg: __('Your application has been submited'), landlord_msg: __('You have a new application'));

        return response()->json([
            'message' => __('Application created successfully.'),
            'data' => $data,
        ]);
    }

    public function show(string $id)
    {
        $data = Application::with(['tenant', 'tenant.user_details', 'property:id,puid,category_id,landlord_id,address_info,rent_info,utility_deposit,security_deposit,late_fee,late_fee_after_days', 'property.propertyDetail:property_id,property_type,property_info', 'property.category:id,name', 'property.landlord:id,name,phone,email', 'property.rent:property_id,id', 'unit'])->findOrFail($id);

        $security_deposit = SecurityDeposit::where('property_id', $data->property_id)->first();

        $security_deposit = [
            'id' => $security_deposit->id ?? null,
            'status' => $security_deposit->status ?? null,
        ];

        $data->setAttribute('security_deposit', $security_deposit);

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $data,
        ]);
    }

    public function agreement_sign(Request $request, string $id)
    {
        $agreement_sign = Application::findOrFail($id);
        $chargeOption = Option::where('key', 'service-charge')->first();
        $chargeData = $chargeOption->value;

        $amount = $chargeData['amount'] ?? 0;
        $type = $chargeData['type'] ?? 'fixed';

        if ($agreement_sign->property->category_id === 6 && $agreement_sign->unit_id) {
            // Unit-based calculation
            $unit = Unit::where('property_id', $agreement_sign->property_id)
                ->findOrFail($agreement_sign->unit_id);

            $deposit_amount = $unit->security_deposit ?? 0;
            $utility_advance = $unit->utility_deposit ?? 0;
            $rent_advance = $unit->monthly_rent ?? 0;

            $base_total_amount = $deposit_amount + $utility_advance + $rent_advance;
        } else {
            // Property-based calculation
            $base_total_amount = ($agreement_sign->property->security_deposit ?? 0)
                + ($agreement_sign->property->rent_info['monthly_rent'] ?? 0)
                + ($agreement_sign->property->utility_deposit ?? 0);
        }

        $calculated_charge = $type === 'percentage'
            ? ($base_total_amount * $amount) / 100
            : $amount;

        DB::beginTransaction();
        try {
            $agreement_sign->update([
                'tenant_agreement' => $request->tenant_agreement
                    ? $this->upload($request, 'tenant_agreement')
                    : null,
            ]);

            $security_deposit = null;
            $unit = null;

            // category-based calculation
            if (in_array($agreement_sign->property->category_id, [1, 2, 3])) {
                $total_amount = ($agreement_sign->property->security_deposit ?? 0)
                                  + ($agreement_sign->property->utility_deposit ?? 0)
                                  + ($agreement_sign->property->rent_info['monthly_rent'] ?? 0);
                $landlord_balance = $total_amount - $calculated_charge;

                $security_deposit = SecurityDeposit::create([
                    'tenant_id' => auth()->user()->id,
                    'landlord_id' => $agreement_sign->property->landlord_id,
                    'property_id' => $agreement_sign->property_id,
                    'deposit_amount' => $agreement_sign->property->security_deposit,
                    'utility_advance' => $agreement_sign->property->utility_deposit,
                    'rent_advance' => $agreement_sign->property->rent_info['monthly_rent'],
                    'total_amount' => $total_amount,
                    'landlord_balance' => $landlord_balance,
                    'charge' => $calculated_charge,
                    'status' => 'unpaid',
                ]);
            } elseif (in_array($agreement_sign->property->category_id, [4, 5, 7, 8])) {
                $total_amount = ($agreement_sign->property->security_deposit ?? 0)
                                  + ($agreement_sign->property->rent_info['monthly_rent'] ?? 0);
                $landlord_balance = $total_amount - $calculated_charge;

                $security_deposit = SecurityDeposit::create([
                    'tenant_id' => auth()->user()->id,
                    'landlord_id' => $agreement_sign->property->landlord_id,
                    'property_id' => $agreement_sign->property_id,
                    'deposit_amount' => $agreement_sign->property->security_deposit,
                    'rent_advance' => $agreement_sign->property->rent_info['monthly_rent'],
                    'total_amount' => $total_amount,
                    'landlord_balance' => $landlord_balance,
                    'charge' => $calculated_charge,
                    'status' => 'unpaid',
                ]);
            } elseif ($agreement_sign->property->category_id === 6) {
                // Unit case
                $unit = Unit::where('property_id', $agreement_sign->property_id)
                    ->findOrFail($agreement_sign->unit_id);

                $deposit_amount = $unit->security_deposit ?? 0;
                $utility_advance = $unit->utility_deposit ?? 0;
                $rent_advance = $unit->monthly_rent ?? 0;

                $total_amount = $deposit_amount + $utility_advance + $rent_advance;
                $landlord_balance = $total_amount - $calculated_charge;

                $security_deposit = SecurityDeposit::create([
                    'tenant_id' => auth()->user()->id,
                    'landlord_id' => $agreement_sign->property->landlord_id,
                    'property_id' => $agreement_sign->property_id,
                    'unit_id' => $unit->id,
                    'deposit_amount' => $deposit_amount,
                    'utility_advance' => $utility_advance,
                    'rent_advance' => $rent_advance,
                    'total_amount' => $total_amount,
                    'landlord_balance' => $landlord_balance,
                    'charge' => $calculated_charge,
                    'status' => 'unpaid',
                ]);
            }

            $rent_total_amount = 0;
            if ($agreement_sign->property->category_id === 6 && $agreement_sign->unit_id) {
                // When agreement has a unit
                $rent_total_amount = ($unit->monthly_rent ?? 0) + ($unit->utility_deposit ?? 0);
            } else {
                // Otherwise use property-level values
                $rent_total_amount = ($agreement_sign->property->rent_info['monthly_rent'] ?? 0)
                                   + ($agreement_sign->property->utility_deposit ?? 0);
            }

            $rent = Rent::create([
                'landlord_id' => $agreement_sign->property->landlord_id,
                'tenant_id' => auth()->user()->id,
                'property_id' => $agreement_sign->property_id,
                'unit_id' => $unit->id ?? null,
                'monthly_rent' => $security_deposit->rent_advance,
                'utility_bill' => $security_deposit->utility_advance,
                'total_amount' => $rent_total_amount,
                'start_date' => $agreement_sign->start_date,
                'end_date' => $agreement_sign->end_date,
                'landlord_agreement' => $agreement_sign->landlord_agreement,
                'tenant_agreement' => $agreement_sign->tenant_agreement,
            ]);

            $security_deposit->update([
                'rent_id' => $rent->id,
            ]);

            DB::commit();

            sendNotification(
                $agreement_sign->id,
                notify_users([$agreement_sign->tenant->id, $agreement_sign->property->landlord->id]),
                landlord_url: route('landlord.tenant-applications.index', ['id' => $agreement_sign->id]),
                tenant_msg: __('You have successfully signed the rental agreement. Awaiting landlord review.'),
                landlord_msg: __('The tenant has signed the rental agreement, please review and proceed with the next steps.')
            );

            return response()->json([
                'message' => 'Agreement signed successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Agreement sign failed',
            ], 500);
        }
    }
}
