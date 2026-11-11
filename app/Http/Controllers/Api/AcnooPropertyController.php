<?php

namespace App\Http\Controllers\Api;

use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyDetail;
use App\Models\Rent;
use App\Models\Review;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AcnooPropertyController extends Controller
{
    use HasUploader;

    public function index(Request $request)
    {
        $query = Property::select('id', 'landlord_id', 'category_id', 'type', 'status', 'room_info', 'rent_info', 'address_info', 'cover_image')
            ->with(['propertyDetail:property_id,property_info', 'units', 'landlord:id,name', 'category:id,name', 'favorite:id,property_id,status'])
            ->when(auth()->user()->role == 'landlord' || request('landlord_id'), function ($q) {
                $q->where('landlord_id', request('landlord_id') ?? auth()->id());
            })
            ->when(auth()->user()->role == 'tenant' && request('show_recommendate') == true, function ($q) {
                $q->whereJsonContains('address_info->state', auth()->user()->user_details->address_info['state']);
            })
            ->when(auth()->user()->role == 'tenant', function ($q) {
                $q->where('status', 1);
            })

            ->when($request->has('status'), function ($q) {
                $q->where('status', request('status'));
            })
            ->when(request('category_id'), function ($q) {
                $q->where('category_id', request('category_id'));
            })
            ->when(request('country'), function ($q) {
                $q->whereJsonContains('address_info->country', request('country'));
            })
            ->when($request->available_rent == true, function ($q) {
                $q->whereDoesntHave('rent');
            })
            ->when(request('type'), function ($q) {
                $q->where('type', request('type'));
            })
            ->when($request->has('search') && $request->search !== null, function ($query) {
                $query->where(function ($q) {
                    $q->where('room_info', 'like', '%'.request('search').'%')
                        ->orWhere('building_name', 'like', '%'.request('search').'%')
                        ->orWhere('address_info', 'like', '%'.request('search').'%')
                        ->orwhereHas('propertyDetail', function ($propertyDetail) {
                            $propertyDetail->where('property_info', 'like', '%'.request('search').'%');
                        })
                        ->orwhereHas('category', function ($category) {
                            $category->where('name', 'like', '%'.request('search').'%');
                        })
                        ->orwhereHas('landlord', function ($category) {
                            $category->where('name', 'like', '%'.request('search').'%');
                        });
                });
            })
            ->when(request('sorting') ?? false, function ($q) {
                $sort = request('sorting');
                if ($sort === 'low_to_high') {
                    $q->orderBy('rent_info->monthly_rent', 'asc');
                } elseif ($sort === 'high_to_low') {
                    $q->orderBy('rent_info->monthly_rent', 'desc');
                }
            }, function ($q) {
                $q->orderBy('created_at', 'desc');
            })
            ->latest();
        if ($request->has('no_paginate') && $request->no_paginate == true) {
            $properties = Property::select('id', 'landlord_id', 'status')
                ->with('propertyDetail:property_id,property_info')
                ->when(auth()->user()->role == 'landlord' || request('landlord_id'), function ($q) {
                    $q->where('landlord_id', request('landlord_id') ?? auth()->id());
                })
                ->when($request->has('status'), function ($q) {
                    $q->where('status', request('status'));
                })
                ->when($request->has('type'), function ($q) {
                    $q->where('type', request('type'));
                })
                ->when($request->available_rent == true, function ($q) {
                    // property based rent check
                    $q->where(function ($query) {
                        $query->whereDoesntHave('rent')
                            ->orWhereHas('rent', function ($subQuery) {
                                $subQuery->whereNotIn('status', ['active', 'processing']);
                            });
                    });
                    // property and unit based rent check
                    $q->orWhere(function ($subQuery) {
                        $subQuery->whereHas('units', function ($unitQuery) {
                            $unitQuery->where(function ($uq) {
                                $uq->whereDoesntHave('rent')
                                    ->orWhereHas('rent', function ($rentQuery) {
                                        $rentQuery->whereNotIn('status', ['active', 'processing']);
                                    });
                            });
                        });
                    });
                })
                ->latest()
                ->get();
            $responseData = [
                'data' => $properties,
            ];
        } else {
            $properties = $query->paginate($request->per_page ?? 10);
            $responseData = $properties;
        }

        return response()->json([
            'message' => 'Property data fetched successfully',
            'data' => $responseData,
        ]);
    }

    public function store(Request $request)
    {
        $plan = plan_data()->plan;
        $property_count = Property::where('landlord_id', auth()->id())->count();

        if ($plan->number_of_property !== -1 && $property_count >= $plan->number_of_property) {
            return response()->json(['message' => __('Your property addition limit has been reached. Your current limit is '.$plan->number_of_property)], 406);
        }

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'house_type_id' => 'nullable|exists:house_types,id',
            'state' => 'nullable',
            'city' => 'nullable',
            'building_name' => 'nullable',
            'utility_deposit' => 'nullable|numeric',
            'security_deposit' => 'nullable|numeric',
            'residential_type' => 'nullable',
            'address' => 'nullable',
            'postcode' => 'nullable|numeric',
            'floor_rang' => 'nullable|string',
            'bedroom' => 'nullable|string',
            'bathroom' => 'nullable|string',
            'property_size' => 'nullable|numeric',
            'furnishing' => 'nullable',
            'facility' => 'nullable',
            'amenity' => 'nullable',
            'property_title' => 'nullable',
            'property_description' => 'nullable',
            'property_rent' => 'nullable|numeric',
            'full_name' => 'required|string',
            'phone' => 'required|array',
            'email' => 'nullable|email',
            'property_type' => 'nullable|string',
            'land_size' => 'nullable|numeric',
            'unit_size' => 'nullable|numeric',
            'unit_number' => 'nullable|string',
            'country' => 'nullable|string',
            'room_size' => 'nullable|string',
            'tenant_preference' => 'nullable',
            'cover_image.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'floorplan_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            DB::beginTransaction();
            $property = Property::create([
                'landlord_id' => auth()->id(),
                'category_id' => $request->category_id,
                'house_type_id' => $request->house_type_id,
                'type' => $request->type,
                'building_name' => $request->building_name,
                'utility_deposit' => $request->utility_deposit ?? 0,
                'security_deposit' => $request->security_deposit ?? 0,
                'sale_amount' => $request->sale_amount ?? 0,
                'late_fee' => $request->late_fee ?? 0,
                'late_fee_after_days' => $request->late_fee_after_days ?? 0,
                'cover_image' => $request->cover_image ? $this->multipleUpload($request, 'cover_image') : null,
                'room_info' => $request->room_info,
                'rent_info' => $request->rent_info,
                'address_info' => $request->address_info,
                'status' => 0,
            ]);

            PropertyDetail::create([
                'property_id' => $property->id,
                'furnishing' => $request->furnishing,
                'facilities' => $request->facilities,
                'parking_lot' => $request->parking_lot,
                'amenities' => $request->amenities,
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'floorplan_image' => $request->floorplan_image ? $this->upload($request, 'floorplan_image') : null,
                'residential_type' => $request->residential_type,
                'property_type' => $request->property_type,
                'property_info' => $request->property_info,
                'tenant_preference' => $request->tenant_preference,
            ]);

            if ($request->category_id == 6) {
                if ($request->has('units') && is_array($request->units)) {
                    $unitFiles = $request->file('units');
                    foreach ($request->units as $index => $unitData) {

                        $images = isset($unitFiles[$index]['images'])
                        ? $this->multipleUploadArray($unitFiles[$index]['images'])
                        : null;

                        Unit::create([
                            'property_id' => $property->id,
                            'unit_name' => $unitData['unit_name'] ?? null,
                            'floor_range' => $unitData['floor_range'] ?? null,
                            'bedroom' => $unitData['bedroom'] ?? null,
                            'bathroom' => $unitData['bathroom'] ?? null,
                            'kitchen' => $unitData['kitchen'] ?? null,
                            'balcony' => $unitData['balcony'] ?? null,
                            'condition' => $unitData['condition'] ?? null,
                            'parking_lot' => $unitData['parking_lot'] ?? null,
                            'unit_size' => $unitData['unit_size'] ?? null,
                            'facilities' => $unitData['facilities'] ?? null,
                            'amenities' => $unitData['amenities'] ?? null,
                            'tenant_preference' => $unitData['tenant_preference'] ?? null,
                            'description' => $unitData['description'] ?? null,
                            'images' => $images,
                            'monthly_rent' => $unitData['monthly_rent'] ?? 0,
                            'sale_amount' => $unitData['sale_amount'] ?? 0,
                            'security_deposit' => $unitData['security_deposit'] ?? 0,
                            'utility_deposit' => $unitData['utility_deposit'] ?? 0,
                            'late_fee' => $unitData['late_fee'] ?? 0,
                            'late_fee_after_days' => $unitData['late_fee_after_days'] ?? 0,
                            'min_rental_value' => $unitData['min_rental_value'] ?? null,
                            'min_rental_unit' => $unitData['min_rental_unit'] ?? null,
                            'security_deposit_value' => $unitData['security_deposit_value'] ?? null,
                            'security_deposit_unit' => $unitData['security_deposit_unit'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            sendNotification($property->id, notify_users([$property->landlord_id]), landlord_url: route('landlord.properties.index', ['id' => $property->id]), admin_msg: __('New property added'), landlord_msg: __('New property has been created'));

            return response()->json([
                'message' => 'Property saved successfully',
                'data' => $property->load('propertyDetail:property_id,property_info', 'landlord:id,name', 'category:id,name'),

            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong',
            ]);
        }
    }

    public function show(string $id)
    {
        $property = Property::with(['propertyDetail', 'units', 'house_type:id,name', 'category:id,name', 'landlord:id,name', 'favorite:id,property_id,status',
            'rent' => function ($query) {
                $query->select('property_id', 'status')
                    ->whereDate('start_date', '<=', Carbon::today())
                    ->whereDate('end_date', '>=', Carbon::today());
            },
            'units' => function ($q) {
                $q->withCount([
                    'rent as is_booked' => function ($rent) {
                        $rent->where('status', 'active')
                            ->whereDate('start_date', '<=', Carbon::today())
                            ->whereDate('end_date', '>=', Carbon::today());
                    },
                ]);
            },
        ])->findOrFail($id);
        $reviews = Review::with('tenant:id,name,image')->where('property_id', $property->id)->latest()->get();

        $can_review = Rent::where('tenant_id', auth()->user()->id)
            ->where('property_id', $property->id)
            ->exists();

        $total = $property->reviews()->count();
        $average = $property->reviews()->avg('review');

        return response()->json([
            'message' => 'Property data fetched successfully',
            'data' => [
                'property' => $property,
                'reviews' => $reviews,
                'can_review' => $can_review,
                'ratings' => [
                    'total' => $total,
                    'average' => $average,
                ],
            ],
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'house_type_id' => 'nullable|exists:house_types,id',
            'state' => 'nullable',
            'city' => 'nullable',
            'building_name' => 'nullable',
            'utility_deposit' => 'nullable|numeric',
            'security_deposit' => 'nullable|numeric',
            'residential_type' => 'nullable',
            'address' => 'nullable',
            'postcode' => 'nullable|numeric',
            'floor_rang' => 'nullable|string',
            'bedroom' => 'nullable|string',
            'bathroom' => 'nullable|string',
            'property_size' => 'nullable|numeric',
            'furnishing' => 'nullable',
            'facilities' => 'nullable',
            'amenities' => 'nullable',
            'property_title' => 'nullable',
            'property_description' => 'nullable',
            'property_rent' => 'nullable|numeric',
            'full_name' => 'required|string',
            'phone' => 'required|array',
            'email' => 'nullable|email',
            'house_type' => 'nullable|string',
            'property_type' => 'nullable|string',
            'land_size' => 'nullable|numeric',
            'unit_size' => 'nullable|numeric',
            'unit_number' => 'nullable|string',
            'country' => 'nullable|string',
            'room_size' => 'nullable|string',
            'tenant_preference' => 'nullable',
            'cover_image.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'floorplan_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $property = Property::findOrFail($id);

            // cover image
            if ($request->cover_removed_images) {
                $cover_prev_images = array_diff($property->cover_image ?? [], $request->cover_removed_images);
                foreach ($request->cover_removed_images as $image) {
                    if (Storage::exists($image)) {
                        Storage::delete($image);
                    }
                }

                $cover_prev_images = array_values($cover_prev_images);
            } else {
                $cover_prev_images = $property->cover_image ?? [];
            }

            $cover_new_images = $request->cover_image ? $this->multipleUpload($request, 'cover_image') : [];

            $cover_merged_images = array_merge($cover_prev_images, $cover_new_images);

            if ($request->hasFile('floorplan_image')) {
                if ($property->propertyDetail->floorplan_image) {
                    Storage::deleteDirectory(dirname($property->propertyDetail->floorplan_image));
                }
                $floorplan_image = $this->upload($request, 'floorplan_image');
            } else {
                $floorplan_image = $property->propertyDetail->floorplan_image;
            }

            $property->update([
                'landlord_id' => auth()->id(),
                'category_id' => $request->category_id,
                'house_type_id' => $request->house_type_id,
                'type' => $request->type,
                'building_name' => $request->building_name,
                'utility_deposit' => $request->utility_deposit ?? 0,
                'security_deposit' => $request->security_deposit ?? 0,
                'sale_amount' => $request->sale_amount ?? 0,
                'late_fee' => $request->late_fee ?? 0,
                'late_fee_after_days' => $request->late_fee_after_days ?? 0,
                'cover_image' => $cover_merged_images,
                'room_info' => $request->room_info,
                'rent_info' => $request->rent_info,
                'address_info' => $request->address_info,
            ]);

            $property->propertyDetail()->update([
                'property_id' => $property->id,
                'furnishing' => $request->furnishing,
                'facilities' => $request->facilities,
                'parking_lot' => $request->parking_lot,
                'amenities' => $request->amenities,
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'floorplan_image' => $floorplan_image,
                'residential_type' => $request->residential_type,
                'property_type' => $request->property_type,
                'property_info' => $request->property_info,
                'tenant_preference' => $request->tenant_preference,
            ]);

            $removedUnitIds = collect($request->input('removed_unit_ids', []))
                ->filter(fn ($v) => ! empty($v))
                ->map(fn ($v) => (int) $v)
                ->unique()
                ->values()
                ->toArray();

            if (! empty($removedUnitIds)) {
                $unitsToDelete = Unit::whereIn('id', $removedUnitIds)->get(['id', 'images']);

                foreach ($unitsToDelete as $u) {
                    if (! empty($u->images) && is_array($u->images)) {
                        foreach ($u->images as $imgPath) {
                            if ($imgPath && Storage::exists($imgPath)) {
                                Storage::delete($imgPath);
                            }
                        }
                    }
                }

                Unit::whereIn('id', $removedUnitIds)->delete();
            }

            if ($request->category_id == 6 && $request->has('units') && is_array($request->units)) {
                $unitFiles = $request->file('units');

                foreach ($request->units as $index => $unitData) {
                    if (empty($unitData['unit_id']) && empty($unitData['unit_name'])) {
                        continue;
                    }

                    $unit = ! empty($unitData['unit_id']) ? Unit::find($unitData['unit_id']) : new Unit;
                    if (! $unit) {
                        $unit = new Unit;
                    }

                    // existing images for this unit
                    $unit_prev_images = $unit->images ?? [];

                    if (! empty($unitData['remove_images']) && is_array($unitData['remove_images'])) {
                        $unit_prev_images = array_diff($unit->images ?? [], $unitData['remove_images']);

                        foreach ($unitData['remove_images'] as $image) {
                            if (Storage::exists($image)) {
                                Storage::delete($image);
                            }
                        }

                        $unit_prev_images = array_values($unit_prev_images);
                    } else {
                        $unit_prev_images = $unit->images ?? [];
                    }

                    $new_images = [];
                    if (isset($unitFiles[$index]['images']) && is_array($unitFiles[$index]['images'])) {
                        $new_images = $this->multipleUploadArray($unitFiles[$index]['images']);
                    }

                    $merged_images = array_merge($unit_prev_images, $new_images);

                    $unit->fill([
                        'property_id' => $property->id,
                        'unit_name' => $unitData['unit_name'] ?? null,
                        'floor_range' => $unitData['floor_range'] ?? null,
                        'bedroom' => $unitData['bedroom'] ?? null,
                        'bathroom' => $unitData['bathroom'] ?? null,
                        'kitchen' => $unitData['kitchen'] ?? null,
                        'balcony' => $unitData['balcony'] ?? null,
                        'condition' => $unitData['condition'] ?? null,
                        'parking_lot' => $unitData['parking_lot'] ?? null,
                        'unit_size' => $unitData['unit_size'] ?? null,
                        'facilities' => $unitData['facilities'] ?? null,
                        'amenities' => $unitData['amenities'] ?? null,
                        'tenant_preference' => $unitData['tenant_preference'] ?? null,
                        'description' => $unitData['description'] ?? null,
                        'images' => $merged_images,
                        'monthly_rent' => $unitData['monthly_rent'] ?? 0,
                        'sale_amount' => $unitData['sale_amount'] ?? 0,
                        'security_deposit' => $unitData['security_deposit'] ?? 0,
                        'utility_deposit' => $unitData['utility_deposit'] ?? 0,
                        'late_fee' => $unitData['late_fee'] ?? 0,
                        'late_fee_after_days' => $unitData['late_fee_after_days'] ?? 0,
                        'min_rental_value' => $unitData['min_rental_value'] ?? null,
                        'min_rental_unit' => $unitData['min_rental_unit'] ?? null,
                        'security_deposit_value' => $unitData['security_deposit_value'] ?? null,
                        'security_deposit_unit' => $unitData['security_deposit_unit'] ?? null,
                    ]);

                    $unit->save();
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Property updated successfully',
                'data' => $property->load('propertyDetail:property_id,property_info', 'landlord:id,name', 'category:id,name'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function update_status(Request $request)
    {
        $property = Property::findOrFail($request->property_id);

        if ($request->is_published == true) {
            $property->update(['status' => 1]);
        } else {
            $property->update(['status' => 2]);
        }

        return response()->json([
            'message' => 'Property status updated successfully',
            'data' => $property,
        ]);
    }

    public function destroy(string $id)
    {
        $property = Property::findOrFail($id);

        if (! empty($property->cover_image)) {
            foreach ($property->cover_image as $image) {
                $oldImagePath = dirname($image);
                Storage::deleteDirectory($oldImagePath);
            }
        }

        if (! empty($property->propertyDetail->floorplan_image)) {
            $oldImagePath = dirname($property->propertyDetail->floorplan_image);
            Storage::deleteDirectory($oldImagePath);
        }

        // Delete unit images
        foreach ($property->units as $unit) {
            if (! empty($unit->images)) {
                foreach ($unit->images as $image) {
                    if ($image && Storage::exists($image)) {
                        Storage::delete($image);
                    }
                }
            }
        }
        $property->delete();

        return response()->json([
            'message' => __('Property deleted successfully'),
        ]);
    }
}
