<?php

namespace App\Exports;

use App\Models\Property;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LandlordPropertiesExport implements FromView
{
    public function view(): View
    {
        $properties = Property::with([
            'category:id,name',
            'propertyDetail:id,property_id,property_info',
            'units.rent',
        ])
            ->where('landlord_id', auth()->id())
            ->when(request('id'), function ($query) {
                $query->where('id', request('id'));
            })
            ->withCount([
                'units as total_unit',
                'units as total_available_unit' => function ($query) {
                    $query->whereDoesntHave('rent', function ($q) {
                        $q->whereIn('status', ['active', 'processing']);
                    });
                },
            ])
            ->latest()
            ->get();

        return view('landlord.properties.excel-csv', [
            'properties' => $properties,
        ]);
    }
}
