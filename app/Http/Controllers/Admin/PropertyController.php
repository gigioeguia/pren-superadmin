<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Channel;
use App\Exports\AdminPropertiesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\StorePropertyRequest;
use App\Http\Requests\Property\UpdatePropertyRequest;
use App\Models\Amenity;
use App\Models\Category;
use App\Models\Facility;
use App\Models\HouseType;
use App\Models\Property;
use App\Models\User;
use App\Services\PropertyService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PropertyController extends Controller
{
    public function __construct(protected PropertyService $service)
    {
        $this->middleware('permission:properties-read')->only('index', 'show', 'acnooFilter', 'exportExcel', 'exportCsv', 'propertySuccess', 'getFacilities', 'getAmenities');
        $this->middleware('permission:properties-create')->only('create', 'store');
        $this->middleware('permission:properties-update')->only('edit', 'update', 'status', 'approve', 'reject');
        $this->middleware('permission:properties-delete')->only('destroy', 'deleteAll');
    }

    public function index()
    {
        $properties = $this->service->listForChannel(Channel::Admin, auth()->user(), ['per_page' => 10]);
        $categories = Category::all();

        return view('admin.properties.index', compact('properties', 'categories'));
    }

    public function getFacilities($landlordId)
    {
        return response()->json($this->service->facilitiesForLandlord($landlordId));
    }

    public function getAmenities($landlordId)
    {
        return response()->json($this->service->amenitiesForLandlord($landlordId));
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

    public function store(StorePropertyRequest $request)
    {
        $actor = $request->user();
        $data = $request->validated();
        $data['unit_files'] = $request->file('units');

        $property = $this->service->store($data, Channel::Admin, $actor, $data['landlord_id'] ?? null);

        return response()->json([
            'message' => 'Property saved successfully',
            'redirect' => route('admin.properties.success', $property->id),
        ]);
    }

    public function show(string $id)
    {
        $property = Property::findOrFail($id);
        $this->authorize('view', $property);

        $property = $this->service->loadForShow($property, Channel::Admin);

        if ($property->category_id == 6) {
            foreach ($property->units as $unit) {
                $unitFacilityIds = $unit->facilities ?? [];
                $unit->setAttribute('facility_names', Facility::whereIn('id', $unitFacilityIds)->pluck('name', 'id'));

                $unitAmenityIds = $unit->amenities ?? [];
                $unit->setAttribute('amenity_names', Amenity::whereIn('id', $unitAmenityIds)->pluck('name', 'id'));
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
        $house_types = HouseType::where('status', 1)->latest()->get();
        $property = Property::with(['category:id,name', 'propertyDetail', 'units'])->findOrFail($id);
        $this->authorize('update', $property);
        $landlords = User::where('role', 'landlord')->latest()->get();

        return view('admin.properties.edit', compact('categories', 'house_types', 'phone_codes', 'countries', 'property', 'landlords'));
    }

    public function update(UpdatePropertyRequest $request, $id)
    {
        $property = Property::findOrFail($id);
        $this->authorize('update', $property);

        $actor = $request->user();
        $data = $request->validated();
        $data['unit_files'] = $request->file('units');

        $property = $this->service->update($property, $data, Channel::Admin, $actor);

        return response()->json([
            'message' => 'Property updated successfully',
            'redirect' => route('admin.properties.index'),
        ]);
    }

    public function propertySuccess(string $id)
    {
        $property = Property::with(['landlord', 'propertyDetail', 'units'])->findOrFail($id);

        return view('admin.properties.property-success', compact('property'));
    }

    public function destroy(string $id)
    {
        $property = Property::findOrFail($id);
        $this->authorize('delete', $property);

        $this->service->delete($property, Channel::Admin);

        return response()->json([
            'message' => __('Property deleted successfully'),
            'redirect' => route('admin.properties.index'),
        ]);
    }

    public function reject(Request $request, string $id)
    {
        $request->validate([
            'meta.reason' => 'required|string|max:255',
        ]);

        $property = Property::findOrFail($id);
        $this->authorize('update', $property);

        $property = $this->service->reject($property, $request->input('meta.reason'));

        return response()->json([
            'message' => 'Property rejected successfully',
            'redirect' => route('admin.properties.show', $property->id),
        ]);
    }

    public function approve(string $id)
    {
        $property = Property::findOrFail($id);
        $this->authorize('update', $property);

        $property = $this->service->approve($property);

        return response()->json([
            'message' => 'Property Approved successfully',
            'redirect' => route('admin.properties.show', $property->id),
        ]);
    }

    public function acnooFilter(Request $request)
    {
        $properties = $this->service->listForChannel(Channel::Admin, auth()->user(), array_merge($request->all(), ['per_page' => 20]));

        return view('admin.properties.datas', ['properties' => $properties]);
    }

    public function status(Request $request, string $id)
    {
        $property = Property::findOrFail($id);
        $this->authorize('update', $property);

        $this->service->setStatus($property, $request->status ?? $request->input('status'));

        return response()->json([
            'message' => 'Status Updated',
            'redirect' => route('admin.properties.index'),
        ]);
    }

    public function deleteAll(Request $request)
    {
        $actor = $request->user();
        $this->service->deleteMany($request->ids ?? [], Channel::Admin, $actor);

        return response()->json([
            'message' => __('Selected property deleted successfully'),
            'redirect' => route('admin.properties.index'),
        ]);
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
