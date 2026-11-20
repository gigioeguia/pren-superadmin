<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\Channel;
use App\Exceptions\PlanLimitExceededException;
use App\Exceptions\PropertyRentedException;
use App\Exports\LandlordPropertiesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\StorePropertyRequest;
use App\Http\Requests\Property\UpdatePropertyRequest;
use App\Models\Amenity;
use App\Models\Category;
use App\Models\Facility;
use App\Models\HouseType;
use App\Models\Property;
use App\Services\PropertyService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PropertyController extends Controller
{
    public function __construct(protected PropertyService $service) {}

    public function index()
    {
        $properties = $this->service->listForChannel(Channel::Landlord, auth()->user(), ['per_page' => 10]);
        $categories = Category::all();

        return view('landlord.properties.index', compact('properties', 'categories'));
    }

    public function create()
    {
        $countries = base_path('lang/countrylist.json');
        $countries = json_decode(file_get_contents($countries), true);
        $phone_codes = base_path('lang/phone_code.json');
        $phone_codes = json_decode(file_get_contents($phone_codes), true);
        $categories = Category::all();
        $facilities = Facility::where('landlord_id', auth()->id())->get();
        $amenities = Amenity::where('landlord_id', auth()->id())->get();
        $house_types = HouseType::where('status', 1)->latest()->get();

        return view('landlord.properties.create', compact('categories', 'facilities', 'amenities', 'countries', 'house_types', 'phone_codes'));
    }

    public function store(StorePropertyRequest $request)
    {
        $actor = $request->user();
        $data = $request->validated();
        $data['unit_files'] = $request->file('units');

        try {
            $property = $this->service->store($data, Channel::Landlord, $actor);
        } catch (PlanLimitExceededException $e) {
            return response()->json(['message' => $e->getMessage()], 406);
        }

        return response()->json([
            'message' => 'Property saved successfully',
            'redirect' => route('landlord.properties.success', $property->id),
        ]);
    }

    public function show(string $id)
    {
        $property = Property::findOrFail($id);
        $this->authorize('view', $property);

        $property = $this->service->loadForShow($property, Channel::Landlord);

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

        $view = $property->category_id == 6 ? 'landlord.properties.unit-view' : 'landlord.properties.view';

        return view($view, compact('property'));
    }

    public function edit(string $id)
    {
        $phone_codes = base_path('lang/phone_code.json');
        $phone_codes = json_decode(file_get_contents($phone_codes), true);
        $countries = base_path('lang/countrylist.json');
        $countries = json_decode(file_get_contents($countries), true);
        $categories = Category::all();
        $facilities = Facility::where('landlord_id', auth()->id())->get();
        $amenities = Amenity::where('landlord_id', auth()->id())->get();
        $house_types = HouseType::where('status', 1)->latest()->get();
        $property = Property::with(['category:id,name', 'propertyDetail'])->findOrFail($id);
        $this->authorize('update', $property);

        return view('landlord.properties.edit', compact('categories', 'facilities', 'amenities', 'house_types', 'phone_codes', 'countries', 'property'));
    }

    public function update(UpdatePropertyRequest $request, string $id)
    {
        $property = Property::findOrFail($id);
        $this->authorize('update', $property);

        $actor = $request->user();
        $data = $request->validated();
        $data['unit_files'] = $request->file('units');

        try {
            $property = $this->service->update($property, $data, Channel::Landlord, $actor);
        } catch (PlanLimitExceededException $e) {
            return response()->json(['message' => $e->getMessage()], 406);
        }

        return response()->json([
            'message' => 'Property updated successfully',
            'redirect' => route('landlord.properties.index'),
        ]);
    }

    public function successView(string $id)
    {
        $property = Property::with(['landlord', 'propertyDetail', 'units'])->findOrFail($id);

        return view('landlord.properties.property-success', compact('property'));
    }

    public function destroy(string $id)
    {
        $property = Property::findOrFail($id);
        $this->authorize('delete', $property);

        try {
            $this->service->delete($property, Channel::Landlord);
        } catch (PropertyRentedException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json([
            'message' => __('Property deleted successfully'),
            'redirect' => route('landlord.properties.index'),
        ]);
    }

    public function deleteAll(Request $request)
    {
        $actor = $request->user();

        try {
            $this->service->deleteMany($request->ids ?? [], Channel::Landlord, $actor);
        } catch (PropertyRentedException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json([
            'message' => __('Selected property deleted successfully'),
            'redirect' => route('landlord.properties.index'),
        ]);
    }

    public function acnooFilter(Request $request)
    {
        $properties = $this->service->listForChannel(Channel::Landlord, auth()->user(), array_merge($request->all(), ['per_page' => 20]));

        return view('landlord.properties.datas', ['properties' => $properties]);
    }

    public function status(Request $request, string $id)
    {
        $property = Property::findOrFail($id);
        $this->authorize('update', $property);

        $property = $this->service->setStatus($property, $request->status ?? $request->input('status'));

        return response()->json([
            'message' => 'Property',
            'redirect' => route('landlord.properties.index'),
        ]);
    }

    public function exportExcel()
    {
        return Excel::download(new LandlordPropertiesExport, 'landlord-properties.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new LandlordPropertiesExport, 'landlord-properties.csv');
    }
}
