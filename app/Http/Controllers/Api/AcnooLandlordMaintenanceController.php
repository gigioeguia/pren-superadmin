<?php

namespace App\Http\Controllers\Api;

use App\Models\Property;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AcnooLandlordMaintenanceController extends Controller
{
    public function rejected(Request $request, string $id)
    {
        $request->validate([
            'notes' => 'nullable|string'
        ]);

        $maintenance = Maintenance::findOrFail($id);

        $maintenance->update([
            'status' => 'rejected',
            'notes' => $request->notes
        ]);

        return response()->json([
            'message' => 'Maintenance rejected successfully',
            'data' => $maintenance
        ]);
    }

    public function processing(Request $request, string $id)
    {

        $request->validate([
            'labor_id' => 'required|integer|exists:labors,id',
            'total_costing' => 'required|numeric',
            'comments' => 'nullable',
        ]);

        $maintenance = Maintenance::findOrFail($id);

        $maintenance->update([
            'labor_id' => $request->labor_id,
            'total_costing' => $request->total_costing,
            'comments' => $request->comments,
            'status' => 'processing',
        ]);

        return response()->json([
            'message' => 'Maintenance processing successfully',
            'data' => $maintenance
        ]);
    }

    public function completed(Request $request, string $id)
    {

        $request->validate([
            'comments' => 'nullable',
        ]);

        $maintenance = Maintenance::findOrFail($id);

        $maintenance->update([
            'comments' => $request->comments,
            'status' => 'completed',
        ]);

        sendNotification($maintenance->id, notify_users([$maintenance->tenant_id, $maintenance->property->landlord_id]), landlord_url: route('landlord.maintenances.index', ['id' => $maintenance->id]),  landlord_msg: __('You have completed a maintenance'), tenant_msg: __('The maintenance has been successfully completed'));

        return response()->json([
            'message' => 'Maintenance completed successfully',
            'data' => $maintenance
        ]);
    }

    public function reports(Request $request)
    {
        $properties  = Property::where('landlord_id', auth()->user()->id)->pluck('id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $propertyId = $request->input('property_id');

        $all_maintenance = Maintenance::whereIn('property_id', $properties);
        if ($fromDate && $toDate) {
            $all_maintenance->whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate);
        }
        $allMaintenance = $all_maintenance->get();

        $data['all_properties'] = [
            'maintenance_total_cost' => $allMaintenance->sum('total_costing'),
            'maintenance_pending' => $allMaintenance->where('status', 'pending')->count(),
            'maintenance_processing' => $allMaintenance->where('status', 'processing')->count(),
            'maintenance_completed' => $allMaintenance->where('status', 'completed')->count(),
            'maintenance_rejected' => $allMaintenance->where('status', 'rejected')->count(),
        ];

        if ($propertyId && in_array($propertyId, $properties->toArray())) {
            $individual_maintenance = Maintenance::where('property_id', $propertyId);
            if ($fromDate && $toDate) {
                $individual_maintenance->whereDate('created_at', '>=', $fromDate)
                    ->whereDate('created_at', '<=', $toDate);
            }
            $individualMaintenance = $individual_maintenance->get();

            $data['individual_property'] = [
                'maintenance_total_cost' => $individualMaintenance->sum('total_costing'),
                'maintenance_pending' => $individualMaintenance->where('status', 'pending')->count(),
                'maintenance_processing' => $individualMaintenance->where('status', 'processing')->count(),
                'maintenance_completed' => $individualMaintenance->where('status', 'completed')->count(),
                'maintenance_rejected' => $individualMaintenance->where('status', 'rejected')->count(),
            ];
        } else {
            $data['individual_property'] = null;
        }

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $data
        ]);
    }
}
