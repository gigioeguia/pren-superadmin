<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\SecurityDeposit;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdminSecurityDepositReportExport;

class AcnooSecurityDepositReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:reports-read')->only('index', 'show');
    }

    public function index()
    {
        $security_deposits = SecurityDeposit::with(['tenant:id,name', 'property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'gateway:id,name'])->latest()->paginate(10);
        return view('admin.reports.security-deposits.index', compact('security_deposits'));
    }

    public function acnooFilter(Request $request)
    {
        $security_deposits = SecurityDeposit::with(['tenant:id,name', 'property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'gateway:id,name'])
            ->when(request('search'), function ($q) {
                $q->where('invoice_no', 'like', '%' . request('search') . '%')
                    ->orWhere('status', 'like', '%' . request('search') . '%')
                    ->orWhere('total_amount', 'like', '%' . request('search') . '%')
                    ->orWhere('utility_advance', 'like', '%' . request('search') . '%');

                $q->orWhereHas('tenant', function ($q) {
                    $q->where('name', 'like', '%' . request('search') . '%');
                });

                $q->orWhereHas('gateway', function ($q) {
                    $q->where('name', 'like', '%' . request('search') . '%');
                });

                $q->orWhereHas('property.propertyDetail', function ($q) {
                    $q->where('property_info', 'like', '%' . request('search') . '%');
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 20);

        if (request()->ajax()) {
            return response()->json([
                'data' => view('admin.reports.security-deposits.datas', compact('security_deposits'))->render()
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
        return Excel::download(new AdminSecurityDepositReportExport, 'security-deposit-reports.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new AdminSecurityDepositReportExport, 'security-deposit-reports.csv');
    }
}
