<?php

namespace App\Http\Controllers\Landlord;

use App\Exports\LandlordRentInvitationExport;
use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Facility;
use App\Models\Option;
use App\Models\Property;
use App\Models\Rent;
use App\Models\SecurityDeposit;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AcnooRentInvitationController extends Controller
{
    use HasUploader;

    public function index(Request $request)
    {
        $tenants = User::with('user_details')->where('landlord_id', auth()->id())->latest()->get();
        $properties = Property::with('rent', 'units')->where('landlord_id', auth()->id())
            ->where('status', 1)
            ->where('type', 'rent')
            ->where(function ($query) {
                $query->where(function ($q) {
                    // Property has multiple units
                    $q->whereHas('units', function ($unitQuery) {
                        $unitQuery->where(function ($uq) {
                            $uq->whereDoesntHave('rent')
                                ->orWhereHas('rent', function ($rq) {
                                    $rq->whereNotIn('status', ['active', 'processing']);
                                });
                        });
                    });
                })
                    ->orWhere(function ($q) {
                        // Property itself has rent (non-unit properties)
                        $q->whereDoesntHave('rent')
                            ->orWhereHas('rent', function ($rq) {
                                $rq->whereNotIn('status', ['active', 'processing']);
                            });
                    });
            })
            ->latest()
            ->get();
        $rents = Rent::select('id', 'monthly_rent', 'status', 'tenant_id', 'property_id', 'unit_id')
            ->with([
                'tenant:id,name,unique_id',
                'property:id,category_id',
                'property.propertyDetail:property_id,property_info,property_type',
                'property.category:id,name',
                'unit:id,unit_name',
                'security_deposit:id,rent_id,deposit_amount',
            ])
            ->where('landlord_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('landlord.rents.index', compact('rents', 'tenants', 'properties'));
    }

    public function acnooFilter(Request $request)
    {
        $rents = Rent::select('id', 'monthly_rent', 'utility_bill', 'total_amount', 'start_date', 'end_date', 'status', 'landlord_id', 'tenant_id', 'property_id')
            ->with([
                'tenant:id,name,unique_id',
                'landlord:id,name',
                'property:id,address_info,rent_info',
                'property.propertyDetail:property_id,property_info,property_type',
                'security_deposit',
            ])
            ->where('landlord_id', auth()->id())
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {

                    $query->whereHas('tenant', function ($query) use ($search) {
                        $query->where('unique_id', 'like', '%'.$search.'%')
                            ->orWhere('name', 'like', '%'.$search.'%');
                    })

                        ->orWhereHas('property.propertyDetail', function ($query) use ($search) {
                            $query->where('property_type', 'like', '%'.$search.'%')
                                ->orWhere('property_info->property_title', 'like', '%'.$search.'%');
                        })

                        ->orWhereHas('security_deposit', function ($query) use ($search) {
                            $query->where('deposit_amount', 'like', '%'.$search.'%');
                        })

                        ->orWhere('monthly_rent', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate($request->input('per_page', 20));

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.rents.datas', compact('rents'))->render(),
            ]);
        }

        return redirect()->back();
    }

    public function getUnit(Request $request)
    {
        $unit = Unit::select('id', 'property_id', 'unit_name')
            ->where('property_id', $request->property_id)
            ->where(function ($query) {
                $query->whereDoesntHave('rent')
                    ->orWhereHas('rent', function ($subQuery) {
                        $subQuery->whereNotIn('status', ['active', 'processing']);
                    });
            })
            ->latest()
            ->get();

        return response()->json($unit);

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

        // rental period validate
        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $rentalDays = $start->diffInDays($end);

        if ($request->unit_id) {
            $unit = Unit::where('property_id', $property->id)
                ->findOrFail($request->unit_id);
            $minValue = $unit->min_rental_value ?? null;
            $minUnit = $unit->min_rental_unit ?? null;

            $deposit_amount = $unit->security_deposit ?? 0;
            $rent_advance = $unit->monthly_rent ?? 0;
            $utility_advance = $unit->utility_deposit ?? 0;
        } else {
            $minValue = $property->rent_info['min_rental_value'] ?? null;
            $minUnit = $property->rent_info['min_rental_unit'] ?? null;

            $deposit_amount = $property->security_deposit ?? 0;
            $rent_advance = $property->rent_info['monthly_rent'] ?? 0;
            $utility_advance = $property->utility_deposit ?? 0;
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

        $chargeOption = Option::where('key', 'service-charge')->first();
        $chargeData = $chargeOption->value;
        $amount = $chargeData['amount'] ?? 0;
        $type = $chargeData['type'] ?? 'fixed';

        // Rent total amount = rent + utility
        $rent_total_amount = $rent_advance + $utility_advance;
        // Security total amount
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
                'landlord_agreement' => $request->landlord_agreement ? $this->upload($request, 'landlord_agreement') : null,
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

            sendNotification($rent->id, notify_users([$rent->tenant->id, $rent->landlord->id]), landlord_url: route('landlord.rents.index', ['id' => $rent->id]), admin_msg: __('Landlord create a new rent invitation'), landlord_msg: __('A new rent invitation has been sent to the tenant. Waiting for approval.'), tenant_msg: __('You have received a new rent invitation. Please review and approve.'));

            return response()->json([
                'message' => 'Rent Invitation sent successfully',
                'redirect' => route('landlord.rents.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'Something went wrong'], 500);
        }

    }

    public function show(string $id)
    {
        $rent = Rent::with([
            'tenant:id,unique_id,name,phone,email',
            'property.propertyDetail',
            'security_deposit:id,rent_id,status',
            'unit',
        ])
            ->findOrFail($id);

        if ($rent->property?->category_id == 6) {
            $unitFacilityIds = $rent->unit?->facilities ?? [];
            $rent->facility_names = Facility::whereIn('id', $unitFacilityIds)->pluck('name', 'id');

            $unitAmenityIds = $rent->unit?->amenities ?? [];
            $rent->amenity_names = Amenity::whereIn('id', $unitAmenityIds)->pluck('name', 'id');
        } else {
            $facilityIds = $rent->property?->propertyDetail?->facilities ?? [];
            $rent->facilities = Facility::whereIn('id', $facilityIds)->pluck('name', 'id');

            $amenityIds = $rent->property?->propertyDetail?->amenities ?? [];
            $rent->amenities = Amenity::whereIn('id', $amenityIds)->pluck('name', 'id');
        }

        return view('landlord.rents.view', compact('rent'));
    }

    public function approved(string $id)
    {
        try {
            $rent_approved = Rent::findOrFail($id);

            $security_deposit = SecurityDeposit::where('rent_id', $rent_approved->id)->first();

            if (! $security_deposit) {
                return response()->json(['message' => 'Security Deposit not found.'], 404);
            }

            if ($security_deposit->status == 'paid') {
                $rent_approved->update([
                    'status' => 'active',
                ]);

                return response()->json([
                    'message' => 'Rent approved successfully',
                    'redirect' => route('landlord.rents.index'),
                ]);
            } else {
                return response()->json(['message' => 'Security Deposit is unpaid.'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'An unexpected error occurred.', 'error' => $e->getMessage()], 500);
        }
    }

    public function canceled(Request $request, string $id)
    {
        $request->validate([
            'title' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $rent = Rent::findOrFail($id);

        if ($rent->status == 'active') {

            $rent->update([
                'cancel_info' => [
                    'title' => $request->title,
                    'reason' => $request->reason,
                    'prev_end_date' => $rent->end_date,
                ],
                'status' => 'cancelled',
            ]);

            sendNotification($rent->id, notify_users([$rent->tenant->id, $rent->landlord->id]), landlord_url: route('landlord.rents.index', ['id' => $rent->id]), landlord_msg: __('The tenant has cancelled the rent agreement.'), tenant_msg: __('You have successfully cancelled your rent agreement.'));

            return response()->json([
                'message' => 'Rent cancelled successfully',
                'redirect' => route('landlord.rents.index'),
            ]);
        } else {
            return response()->json(['message' => 'Rent not found'], 404);
        }
    }

    public function exportExcel()
    {
        return Excel::download(new LandlordRentInvitationExport, 'landlord-rent-invitation.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new LandlordRentInvitationExport, 'landlord-rent-invitation.csv');
    }
}
