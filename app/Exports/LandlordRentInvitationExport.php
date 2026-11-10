<?php

namespace App\Exports;

use App\Models\Rent;
use App\Models\User;
use App\Models\Property;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LandlordRentInvitationExport implements FromView
{
    public function view(): View
    {
        $tenants = User::with('user_details')->where('landlord_id', auth()->id())->latest()->get();
        $properties = Property::where('landlord_id', auth()->id())->whereDoesntHave('rent')->latest()->get();
        $rents = Rent::select('id', 'monthly_rent', 'utility_bill', 'total_amount', 'start_date', 'end_date', 'status', 'landlord_id', 'tenant_id', 'property_id')
            ->with('tenant:id,name,unique_id', 'landlord:id,name', 'property:id,address_info,rent_info', 'property.propertyDetail:property_id,property_info,property_type', 'security_deposit')
            ->where('landlord_id', auth()->id())
            ->latest()
            ->get();

        return view('landlord.properties.excel-csv', [
            'tenants' => $tenants,
            'properties' => $properties,
            'rents' => $rents,
        ]);
    }
}
