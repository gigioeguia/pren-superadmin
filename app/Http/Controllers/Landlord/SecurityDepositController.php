<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\Channel;
use App\Exports\LandlordSecurityDepositExport;
use App\Http\Controllers\Controller;
use App\Models\SecurityDeposit;
use App\Services\SecurityDepositService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SecurityDepositController extends Controller
{
    public function __construct(protected SecurityDepositService $service) {}

    public function index()
    {
        $security_deposits = $this->service->listForChannel(Channel::Landlord, auth()->user(), ['per_page' => 10]);

        return view('landlord.security-deposits.index', compact('security_deposits'));
    }

    public function show(string $id)
    {
        $security_deposit = SecurityDeposit::with(['landlord:id,name,phone,email', 'landlord.user_details:user_id,address_info', 'tenant:id,name,phone,email', 'tenant.user_details:user_id,address_info'])->findOrFail($id);
        $this->authorize('view', $security_deposit);

        return view('landlord.security-deposits.view', compact('security_deposit'));
    }

    public function filter(Request $request)
    {
        $security_deposits = $this->service->listForChannel(Channel::Landlord, auth()->user(), array_merge($request->all(), ['per_page' => $request->per_page ?? 20]));

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.security-deposits.datas', ['security_deposits' => $security_deposits])->render(),
            ]);
        }

        return redirect(url()->previous());
    }

    // Restaurado (spec 5.17): filename verbatim pre-4.2.
    public function exportExcel()
    {
        return Excel::download(new LandlordSecurityDepositExport, 'security-deposits.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new LandlordSecurityDepositExport, 'security-deposits.csv');
    }
}
