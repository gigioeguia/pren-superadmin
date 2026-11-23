<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Channel;
use App\Exports\AdminPropertiesExport;
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
    public function __construct(protected PropertyService $service)
    {
        $this->middleware('permission:properties-read')->only('index', 'show', 'filter', 'exportExcel', 'exportCsv', 'propertySuccess', 'getFacilities', 'getAmenities');
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
        return view('admin.properties.create', $this->service->formOptions(Channel::Admin));
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

        $property = $this->service->decorateForShow(
            $this->service->loadForShow($property, Channel::Admin)
        );

        $view = $property->category_id == 6 ? 'admin.properties.unit-view' : 'admin.properties.view';

        return view($view, compact('property'));
    }

    public function edit(string $id)
    {
        $property = Property::with(['category:id,name', 'propertyDetail', 'units'])->findOrFail($id);
        $this->authorize('update', $property);

        return view('admin.properties.edit', $this->service->formOptions(Channel::Admin, property: $property));
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

    public function filter(Request $request)
    {
        $properties = $this->service->listForChannel(Channel::Admin, auth()->user(), array_merge($request->all(), ['per_page' => 20]));

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.properties.datas', ['properties' => $properties])->render(),
            ]);
        }

        return redirect(url()->previous());
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

    public function exportExcel(Request $request)
    {
        return Excel::download(new AdminPropertiesExport($request), 'properties.xlsx');
    }

    public function exportCsv(Request $request)
    {
        return Excel::download(new AdminPropertiesExport($request), 'properties.csv');
    }
}
