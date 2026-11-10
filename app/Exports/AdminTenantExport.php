<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AdminTenantExport implements FromView
{
    protected $status;

    public function __construct($status)
    {
        $this->status = $status;
    }

    public function view(): View
    {
        $tenants = User::with([
            'user_details',
            'rents',
            'rents.property:id',
            'rents.property.propertyDetail:property_id,property_info'
        ])
            ->where('role', 'tenant')
            ->when($this->status, function ($query) {
                if ($this->status === 'active') {
                    $query->whereHas('rents', function ($rentsQuery) {
                        $rentsQuery->where('status', 'active');
                    });
                } elseif ($this->status === 'expired') {
                    $query->whereHas('rents', function ($rentsQuery) {
                        $rentsQuery->where('status', 'expired');
                    })->whereDoesntHave('rents', function ($rentsQuery) {
                        $rentsQuery->where('status', 'active');
                    });
                }
            }, function ($query) {
                $query->whereDoesntHave('rents'); // Default case: only tenants with NO rents
            })
            ->latest()
            ->get();

        return view('admin.tenants.excel-csv', [
            'tenants' => $tenants
        ]);
    }
}
