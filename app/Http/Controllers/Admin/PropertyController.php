<?php

namespace App\Http\Controllers\Admin;

use App\Models\Unit;
use App\Models\User;
use App\Models\Amenity;
use App\Models\Category;
use App\Models\Facility;
use App\Models\Property;
use App\Models\HouseType;
use App\Helpers\HasUploader;
use Illuminate\Http\Request;
use App\Models\PropertyDetail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdminPropertiesExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PropertyController extends Controller
{
    use HasUploader;

    public function __construct()
    {
        $this->middleware('permission:properties-read')->only('index', 'show');
        $this->middleware('permission:properties-create')->only('create', 'store');
        $this->middleware('permission:properties-update')->only('edit', 'update', 'status', 'approve', 'reject');
        $this->middleware('permission:properties-delete')->only('destroy', 'deleteAll');
    }

    public function index()
    {
        $properties = Property::with([
                'category:id,name',
                'landlord:id,name',
                'propertyDetail:property_id,property_info',
                'units.rent'
            ])
            ->when(request('id'), function ($query) {
                $query->where('id', request('id'));
            })
            ->withCount([
                'units as total_unit',
                'units as total_available_unit' => function ($query) {
                    $query->whereDoesntHave('rent', function ($q) {
                        $q->whereIn('status', ['active', 'processing']);
                    });
                }
            ])
            ->latest()
            ->paginate(10);

        $categories = Category::all();

        return view('admin.properties.index', compact('properties', 'categories'));
    }

    public function acnooFilter(Request $request)
    {
        $properties = Property::with(['category:id,name', 'landlord:id,name', 'propertyDetail:property_id,property_info', 'units.rent'])
            ->withCount([
                'units as total_unit',
                'units as total_available_unit' => function ($query) {
                    $query->whereDoesntHave('rent', function ($q) {
                        $q->whereIn('status', ['active', 'processing']);
                    });
                }
            ])
            ->when(request('category_id'), function ($q) {
                $q->where('category_id', request('category_id'));
            })
            ->when(request('search'), function ($q) {
                $q->where('rent_info', 'like', '%' . request('search') . '%')
                    ->orwhere('puid', 'like', '%' . request('search') . '%')
                    ->orWhere('address_info->address', 'like', '%' . request('search') . '%')
                    ->orWhere('address_info->state', 'like', '%' . request('search') . '%')
                    ->orWhere('address_info->city', 'like', '%' . request('search') . '%')
                    ->orWhere('rent_info->monthly_rent', 'like', '%' . request('search') . '%');

                $q->orWhereHas('category', function ($q) {
                    $q->where('name', 'like', '%' . request('search') . '%');
                });

                $q->orWhereHas('propertyDetail', function ($q) {
                    $q->where('property_info', 'like', '%' . request('search') . '%');
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 20);

        if (request()->ajax()) {
            return response()->json([
                'data' => view('admin.properties.datas', compact('properties'))->render()
            ]);
        }
        return redirect(url()->previous());
    }

    public function getFacilities($landlordId)
    {
        $facilities = Facility::where('landlord_id', $landlordId)->get();

        return response()->json($facilities);
    }

    public function getAmenities($landlordId)
    {
        $amenities = Amenity::where('landlord_id', $landlordId)->get();

        return response()->json($amenities);
    }

    public function create()
    {
        $countries = base_path('lang/countrylist.json');
        $countries = json_decode(file_get_contents($countries), true);
        $phone_codes = base_path('lang/phone_code.json');
        $phone_codes = json_decode(file_get_contents($phone_codes), true);
        $categories = Category::all();
        $house_types = HouseType::where('status', 1)->latest()->get();
        $landlords = User::where('role', 'landlord')->latest()->get();

        return view('admin.properties.create', compact('categories', 'countries', 'house_types', 'phone_codes', 'landlords'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'landlord_id' => 'required|exists:users,id',
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
                'landlord_id' => $request->landlord_id,
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
                'room_info' =>  $request->room_info,
                'rent_info' => $request->rent_info,
                'address_info' => $request->address_info,
                'status' => 1,
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
                            'unit_name' => $unitData['unit_name'] ?? 'test',
                            'floor_range' => $unitData['floor_range'] ?? null,
                            'bedroom' => $unitData['bedroom'] ?? null,
                            'bathroom' => $unitData['bathroom'] ?? null,
                            'kitchen' => $unitData['kitchen'] ?? null,
                            'balcony' => $unitData['balcony'] ?? null,
                            'condition' => $unitData['condition'] ?? null,
                            'parking_lot' => $unitData['parking_lot'] ?? null,
                            'unit_size' => $unitData['unit_size'] ?? null,
                            'facilities' =>  $unitData['facilities'] ?? null,
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

            sendNotification(
                $property->id,
                notify_users([$property->landlord->id]),
                admin_url: route('admin.properties.index', ['id' => $property->id]),
                landlord_url: route('landlord.properties.index', ['id' => $property->id]),
                admin_msg: __('A new property has been created.'),
                landlord_msg: __('Your property has been successfully added.')
            );

            return response()->json([
                'message' => 'Property saved successfully',
                'redirect' => route('admin.properties.success', $property->id),
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
        $property = Property::with([
            'category:id,name',
            'propertyDetail',
            'units',
            'landlord:id,name',
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
                    }
                ]);
            }
        ])->findOrFail($id);

        if ($property->category_id == 6) {
            foreach ($property->units as $unit) {
                $unitFacilityIds = $unit->facilities ?? [];
                $unit->facility_names = Facility::whereIn('id', $unitFacilityIds)->pluck('name', 'id');

                $unitAmenityIds = $unit->amenities ?? [];
                $unit->amenity_names = Amenity::whereIn('id', $unitAmenityIds)->pluck('name', 'id');
            }
        } else {
            $facilityIds = $property->propertyDetail->facilities ?? [];
            $property->facilities = Facility::whereIn('id', $facilityIds)->pluck('name', 'id');

            $amenityIds = $property->propertyDetail->amenities ?? [];
            $property->amenities = Amenity::whereIn('id', $amenityIds)->pluck('name', 'id');
        }

        $view = $property->category_id == 6 ? 'admin.properties.unit-view' : 'admin.properties.view';

        return view($view, compact('property'));
    }


    public function edit(string $id)
    {
        $phone_codes = base_path('lang/phone_code.json');
        $phone_codes = json_decode(file_get_contents($phone_codes), true);
        $countries = base_path('lang/countrylist.json');
        $countries = json_decode(file_get_contents($countries), true);
        $categories = Category::all();
        $house_types  = HouseType::where('status', 1)->latest()->get();
        $property = Property::with(['category:id,name', 'propertyDetail', 'units'])->findOrFail($id);
        $landlords = User::where('role', 'landlord')->latest()->get();

        return view('admin.properties.edit', compact('categories', 'house_types', 'phone_codes', 'countries', 'property', 'landlords'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'house_type_id' => 'nullable|exists:house_types,id',
            'landlord_id' => 'required|exists:users,id',
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

            //cover image
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
                'landlord_id' => $request->landlord_id,
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
                'room_info' =>  $request->room_info,
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

            $receivedUnitIds = collect($request->units)
                                ->pluck('unit_id')
                                ->filter()
                                ->toArray();

            $existingUnitIds = $property->units()->pluck('id')->toArray();

            $unitsToDeleteIds = array_diff($existingUnitIds, $receivedUnitIds);

            if (!empty($unitsToDeleteIds)) {
                $unitsToDelete = Unit::whereIn('id', $unitsToDeleteIds)->get(['id', 'images']);

                foreach ($unitsToDelete as $u) {
                    if (!empty($u->images) && is_array($u->images)) {
                        foreach ($u->images as $imgPath) {
                            if ($imgPath && Storage::exists($imgPath)) {
                                Storage::delete($imgPath);
                            }
                        }
                    }
                }

                Unit::whereIn('id', $unitsToDeleteIds)->delete();
            }


            if ($request->category_id == 6 && $request->has('units') && is_array($request->units)) {
                $unitFiles = $request->file('units');

                 foreach ($request->units as $index => $unitData) {
                    if (empty($unitData['unit_id']) && empty($unitData['unit_name'])) {
                        continue;
                    }

                    $unit = !empty($unitData['unit_id']) ? Unit::find($unitData['unit_id']) : new Unit();
                    if (!$unit) {
                        $unit = new Unit();
                    }

                    // existing images for this unit
                    $unit_prev_images = $unit->images ?? [];

                    if (!empty($unitData['remove_images']) && is_array($unitData['remove_images'])) {
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
                'redirect' => route('admin.properties.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong',
            ], 500);
        }

    }

    public function propertySuccess(string $id)
    {
        $property = Property::with(['landlord', 'propertyDetail', 'units'])->findOrFail($id);
        return view('admin.properties.property-success', compact('property'));
    }

    public function status(Request $request, string $id)
    {

        $property = Property::findOrFail($id);
        $property->update([
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'Status Updated',
            'redirect' => route('admin.properties.index')
        ]);
    }

    public function destroy(string $id)
    {
        $property = Property::findOrFail($id);

        if (!empty($property->cover_image)) {
            foreach ($property->cover_image as $image) {
                $oldImagePath = dirname($image);
                Storage::deleteDirectory($oldImagePath);
            }
        }

        if (!empty($property->propertyDetail->floorplan_image)) {
            $oldImagePath = dirname($property->propertyDetail->floorplan_image);
            Storage::deleteDirectory($oldImagePath);
        }

        foreach ($property->units as $unit) {
            if (!empty($unit->images)) {
                foreach ($unit->images as $image) {
                    if ($image && Storage::exists($image)) {
                        Storage::delete($image);
                    }
                }
            }
        }

        $property->delete();

        return response()->json([
            'message'   => __('Property deleted successfully'),
            'redirect'  => route('admin.properties.index')
        ]);
    }

    public function deleteAll(Request $request)
    {
        $properties = Property::whereIn('id', $request->ids)->get();

        foreach ($properties as $property) {
            if (!empty($property->cover_image)) {
                foreach ($property->cover_image as $image) {
                    $oldImagePath = dirname($image);
                    Storage::deleteDirectory($oldImagePath);
                }
            }

            if (!empty($property->propertyDetail->floorplan_image)) {
                $oldImagePath = dirname($property->propertyDetail->floorplan_image);
                Storage::deleteDirectory($oldImagePath);
            }

            foreach ($property->units as $unit) {
                if (!empty($unit->images)) {
                    foreach ($unit->images as $image) {
                        if ($image && Storage::exists($image)) {
                            Storage::delete($image);
                        }
                    }
                }
            }
            $property->delete();
        }
        return response()->json([
            'message'   => __('Selected property deleted successfully'),
            'redirect'  => route('admin.properties.index')
        ]);
    }

    public function reject(Request $request, string $id)
    {
        $request->validate([
            'meta.reason' => 'required|string|max:255',
        ]);

        $property = Property::findOrFail($id);

        if ($property) {

            $meta = $property->meta ?? [];

            $meta['reason'] = $request->input('meta.reason');

            $property->update([
                'status' => '3',
                'meta' => $meta,
            ]);

            return response()->json([
                'message' => 'Property rejected successfully',
                'redirect' => route('admin.properties.show', $property->id)
            ]);
        } else {
            return response()->json(['message' => 'Property rejected Not Found'], 404);
        }
    }

    public function approve(string $id)
    {
        $property = Property::findOrFail($id);

        if ($property) {

            $property->update([
                'status' => '1',
            ]);

            return response()->json([
                'message' => 'Property Approved successfully',
                'redirect' => route('admin.properties.show', $property->id)
            ]);
        } else {
            return response()->json(['message' => 'Property Not Found'], 404);
        }
    }

    public function exportExcel()
    {
        return Excel::download(new AdminPropertiesExport, 'properties.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new AdminPropertiesExport, 'properties.csv');
    }
}
