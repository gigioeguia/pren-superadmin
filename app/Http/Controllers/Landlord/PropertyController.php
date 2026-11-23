<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\Channel;
use App\Exceptions\PlanLimitExceededException;
use App\Exceptions\PropertyRentedException;
use App\Exports\LandlordPropertiesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\StorePropertyRequest;
use App\Http\Requests\Property\UpdatePropertyRequest;
use App\Models\Category;
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
        return view('landlord.properties.create', $this->service->formOptions(Channel::Landlord, auth()->user()));
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

        $property = $this->service->decorateForShow(
            $this->service->loadForShow($property, Channel::Landlord)
        );

        $view = $property->category_id == 6 ? 'landlord.properties.unit-view' : 'landlord.properties.view';

        return view($view, compact('property'));
    }

    public function edit(string $id)
    {
        $property = Property::with(['category:id,name', 'propertyDetail'])->findOrFail($id);
        $this->authorize('update', $property);

        return view('landlord.properties.edit', $this->service->formOptions(Channel::Landlord, auth()->user(), $property));
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

    public function filter(Request $request)
    {
        $properties = $this->service->listForChannel(Channel::Landlord, auth()->user(), array_merge($request->all(), ['per_page' => 20]));

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.properties.datas', ['properties' => $properties])->render(),
            ]);
        }

        return redirect(url()->previous());
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

    public function exportExcel(Request $request)
    {
        return Excel::download(new LandlordPropertiesExport($request), 'landlord-properties.xlsx');
    }

    public function exportCsv(Request $request)
    {
        return Excel::download(new LandlordPropertiesExport($request), 'landlord-properties.csv');
    }
}
