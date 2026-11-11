<?php

namespace App\Http\Controllers\Landlord;

use App\Exports\LandlordSecurityDepositExport;
use App\Http\Controllers\Controller;
use App\Models\SecurityDeposit;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AcnooSecurityDepositController extends Controller
{
    public function index()
    {
        $security_deposits = SecurityDeposit::with(['tenant:id,name', 'property:id,puid',  'property.propertyDetail:id,property_id,property_info'])->where('landlord_id', auth()->id())->latest()->paginate(10);

        return view('landlord.security-deposits.index', compact('security_deposits'));
    }

    public function acnooFilter(Request $request)
    {
        $security_deposits = SecurityDeposit::with(['tenant:id,name', 'property:id,puid',  'property.propertyDetail:id,property_id,property_info'])
            ->where('landlord_id', auth()->id())
            ->when(request('search'), function ($q) {
                $q->where(function ($q) {
                    $search = request('search');
                    $q->where('invoice_no', 'like', '%'.$search.'%')
                        ->orwhere('deposit_amount', 'like', '%'.$search.'%')
                        ->orWhereHas('tenant', function ($query) use ($search) {
                            $query->where('name', 'like', '%'.$search.'%');
                        })

                        ->orWhereHas('property', function ($query) use ($search) {
                            $query->where('puid', 'like', '%'.$search.'%');
                        })

                        ->orWhereHas('property.propertyDetail', function ($query) use ($search) {
                            $query->where('property_info->property_title', 'like', '%'.$search.'%');
                        });
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 20);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.security-deposits.datas', compact('security_deposits'))->render(),
            ]);
        }

        return redirect(url()->previous());
    }

    public function show(string $id)
    {
        $security_deposit = SecurityDeposit::with(['landlord:id,name,phone,email', 'landlord.user_details:user_id,address_info',  'tenant:id,name,phone,email', 'tenant.user_details:user_id,address_info'])->findOrFail($id);

        return view('landlord.security-deposits.view', compact('security_deposit'));
    }

    public function exportExcel()
    {
        return Excel::download(new LandlordSecurityDepositExport, 'security-deposits.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new LandlordSecurityDepositExport, 'security-deposits.csv');
    }
}
