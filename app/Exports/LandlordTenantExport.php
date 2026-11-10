<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LandlordTenantExport implements FromView
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $landlordId = auth()->id();
        $status = $this->request->status; // Get status from request

        $tenants = User::with('user_details')
            ->where('landlord_id', $landlordId)
            ->when($status, function ($query) use ($status) {
                $query->whereHas('rents', function ($rentsQuery) use ($status) {
                    $rentsQuery->where('status', $status);
                });
            })
            ->latest()
            ->get();

        return view('landlord.tenants.excel-csv', [
            'tenants' => $tenants
        ]);
    }
}

