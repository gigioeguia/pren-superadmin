<?php

namespace App\Exports;

use App\Models\Property;
use App\Models\Application;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LandlordApplicationExport implements FromView
{
    public function view(): View
    {
        $property = Property::where('landlord_id', auth()->id())->get('id');
        $applications = Application::with(['tenant:id,name', 'property:id,puid', 'property.propertyDetail:property_id,property_info', 'unit:id,unit_name'])->whereIn('property_id', $property)->latest()->get();
        return view('landlord.application.excel-csv', [
            'applications' => $applications
        ]);
    }
}
