<?php

namespace App\Http\Controllers\Landlord;

use App\Models\Property;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LandlordMaintenanceReportExport;

class AcnooMaintenanceReportController extends Controller
{
    public function index(Request $request)
    {
        $properties  = Property::where('landlord_id', auth()->user()->id)->pluck('id');
        $maintenances = Maintenance::whereIn('property_id', $properties)->with('property.propertyDetail:property_id,property_info')->latest()->paginate(10);
        $processingCount = Maintenance::whereIn('property_id', $properties)->where('status', 'processing')->count();
        $completedCount = Maintenance::whereIn('property_id', $properties)->where('status', 'completed')->count();
        $rejectedCount = Maintenance::whereIn('property_id', $properties)->where('status', 'rejected')->count();
        $pendingCount = Maintenance::whereIn('property_id', $properties)->where('status', 'pending')->count();

        return view('landlord.maintenance-reports.index', compact('maintenances', 'pendingCount', 'processingCount', 'completedCount', 'rejectedCount'));
    }

    public function acnooFilter(Request $request)
    {
        $properties = Property::where('landlord_id', auth()->user()->id)->pluck('id');

        $maintenances = Maintenance::with(['property.propertyDetail:id,property_id,property_info'])
            ->whereIn('property_id', $properties)
            ->when($request->search, function ($q) use ($request) {
                $search = $request->search;
                $q->where('total_costing', 'like', '%' . $search . '%')
                    ->orWhereHas('property.propertyDetail', function ($query) use ($search) {
                        $query->where('property_info->property_title', 'like', '%' . $search . '%');
                    });
            })
            ->latest()
            ->paginate($request->per_page ?? 20);

        // Count variables
        $processingCount = Maintenance::whereIn('property_id', $properties)->where('status', 'processing')->count();
        $completedCount = Maintenance::whereIn('property_id', $properties)->where('status', 'completed')->count();
        $rejectedCount = Maintenance::whereIn('property_id', $properties)->where('status', 'rejected')->count();
        $pendingCount = Maintenance::whereIn('property_id', $properties)->where('status', 'pending')->count();

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.maintenance-reports.datas', compact(
                    'maintenances',
                    'pendingCount',
                    'processingCount',
                    'completedCount',
                    'rejectedCount'
                ))->render()
            ]);
        }

        return redirect(url()->previous());
    }

    public function exportExcel()
    {
        return Excel::download(new LandlordMaintenanceReportExport, 'landlord-maintenance-reports.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new LandlordMaintenanceReportExport, 'landlord-maintenance-reports.csv');
    }
}
