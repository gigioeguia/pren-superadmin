<?php

namespace App\Http\Controllers\Api;

use App\Models\Maintenance;
use App\Helpers\HasUploader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class AcnooTenantMaintenanceController extends Controller
{
    use HasUploader;

    public function index(Request $request)
    {
        $maintenances = Maintenance::select('id', 'tenant_id', 'property_id', 'title', 'description', 'status', 'created_at')
            ->with('tenant:id,name,image', 'property:id,cover_image', 'property.propertyDetail:property_id,property_info')
            ->when(auth()->user()->role ==  'tenant', function ($q) {
                $q->where('tenant_id', auth()->id());
            })
            ->when(auth()->user()->role == 'landlord' || $request->landlord_id, function ($q) use ($request) {
                $q->whereHas('property', function ($property) use ($request) {
                    $property->where('landlord_id', $request->landlord_id ?? auth()->id());
                });
            })
            ->when($request->has('status') && $request->status !== NULL, function ($query) use ($request) {
                $query->where('status', $request->status);
            })

            ->when($request->has('search') && $request->search !== null, function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%')
                        ->orwhereHas('property.propertyDetail', function ($propertyDetail) {
                            $propertyDetail->where('property_info', 'like', '%' . request('search') . '%');
                        })
                        ->orwhereHas('tenant', function ($tenant) {
                            $tenant->where('name', 'like', '%' . request('search') . '%');
                        });
                });
            })
            ->latest()->paginate($request->per_page ?? 10);

        return response()->json([
            'message' => 'Data fetched successfully.',
            'data' => $maintenances
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'property_id' => 'required|exists:properties,id',
            'description' => 'nullable|string',
            'images' => 'nullable|array',
        ]);

        $maintenance = Maintenance::create($request->except('images', 'tenant_id') + [
            'tenant_id' => auth()->id(),
            'images' => $request->images ? $this->multipleUpload($request, 'images') : null,
        ]);

        sendNotification($maintenance->id, notify_users([$maintenance->property->landlord->id, $maintenance->tenant->id]), landlord_url: route('landlord.maintenances.index', ['id' => $maintenance->id]),  admin_msg: __('New maintenance request has been created'), landlord_msg: __('You have new maintenance request.'), tenant_msg: __('Your maintenance request has been send'));

        return response()->json([
            'message' => 'Maintenance request has been submitted successfully.',
            'data' => $maintenance
        ]);
    }

    public function show(string $id)
    {
        $maintenance = Maintenance::with(['tenant:id,name,image,phone', 'labor:id,name', 'property:id,cover_image,address_info', 'property.propertyDetail:property_id,property_info'])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Data fetched successfully.',
            'data' => $maintenance
        ]);
    }

    public function update(Request $request, string $id)
    {
        $maintenance = Maintenance::findOrFail($id);

        $request->validate([
            'title' => 'required|string',
            'property_id' => 'required|exists:properties,id',
            'description' => 'nullable|string',
            'images' => 'nullable|array',
        ]);

        if ($maintenance->status == 'processing' || $maintenance->status == 'completed') {
            return response()->json([
                'message' => 'You can not update maintenance request.',
            ]);
        }

        if ($request->removed_images) {
            $prev_images = array_diff($maintenance->images ?? [], $request->removed_images);
            foreach ($request->removed_images as $image) {
                if (Storage::exists($image)) {
                    Storage::delete($image);
                }
            }

            $prev_images = array_values($prev_images);
        } else {
            $prev_images = $maintenance->images ?? [];
        }

        $new_images = $request->images ? $this->multipleUpload($request, 'images') : [];

        $merged_images = array_merge($prev_images, $new_images);

        $maintenance->update($request->except('images', 'tenant_id') + [
            'tenant_id' => auth()->id(),
            'images' => $merged_images
        ]);

        return response()->json([
            'message' => 'Maintenance updated successfully.',
            'data' => $maintenance
        ]);
    }
}
