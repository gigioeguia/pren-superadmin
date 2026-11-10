<?php

namespace App\Exports;

use App\Models\Property;
use App\Models\Maintenance;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LandlordMaintenanceReportExport implements FromView
{
    public function view(): View
    {
        $properties  = Property::where('landlord_id', auth()->user()->id)->pluck('id');
        $maintenances = Maintenance::whereIn('property_id', $properties)->with(['property.propertyDetail:property_id,property_info'])->latest()->get();
        $processingCount = Maintenance::whereIn('property_id', $properties)->where('status', 'processing')->count();
        $completedCount = Maintenance::whereIn('property_id', $properties)->where('status', 'completed')->count();
        $rejectedCount = Maintenance::whereIn('property_id', $properties)->where('status', 'rejected')->count();
        $pendingCount = Maintenance::whereIn('property_id', $properties)->where('status', 'pending')->count();

        return view('landlord.maintenance-reports.excel-csv', [
            'maintenances' => $maintenances,
            'processingCount' => $processingCount,
            'completedCount' => $completedCount,
            'rejectedCount' => $rejectedCount,
            'pendingCount' => $pendingCount
        ]);
    }
}
