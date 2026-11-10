<?php

namespace App\Exports;

use App\Models\Property;
use App\Models\Maintenance;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LandlordMaintenanceExport implements FromView
{
    public function view(): View
    {
        $property = Property::where('landlord_id', auth()->id())->get('id');
        $maintenances = Maintenance::with(['tenant:id,name,phone', 'labor:id,name', 'property'])->whereIn('property_id', $property)->latest()->get();

        return view('landlord.maintenance.excel-csv', [
            'maintenances' => $maintenances
        ]);
    }
}
