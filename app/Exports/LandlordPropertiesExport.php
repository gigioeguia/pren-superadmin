<?php

namespace App\Exports;

use App\Models\Property;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromView;

class LandlordPropertiesExport implements FromView
{
    public function __construct(private Request $request) {}

    public function view(): View
    {
        $properties = Property::with([
            'category:id,name',
            'propertyDetail:id,property_id,property_info',
            'units.rent',
        ])
            ->where('landlord_id', auth()->id())
            ->when($this->request->input('id'), function ($query) {
                $query->where('id', $this->request->input('id'));
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
