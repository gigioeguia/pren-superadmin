<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AdminLandlordExport implements FromView
{

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $request = $this->request;

        $landlords = User::with(['user_details', 'plan_subscribe:id,plan_id', 'plan_subscribe.plan'])
            ->where('role', 'landlord')
            ->when($request->id, function ($query) use ($request) {
                $query->where('id', $request->id);
            })
            ->when($request->status, function ($query) use ($request) {
                if ($request->status === 'active') {
                    $query->whereDate('will_expire', '>=', today());
                } elseif ($request->status === 'expired') {
                    $query->whereDate('will_expire', '<=', today());
                }
            })
            ->latest()
            ->get();

        return view('admin.landlords.excel-csv', [
            'landlords' => $landlords
        ]);
    }
}
