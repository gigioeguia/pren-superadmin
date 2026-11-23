<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Channel;
use App\Exports\AdminSecurityDepositExport;
use App\Http\Controllers\Controller;
use App\Models\SecurityDeposit;
use App\Services\SecurityDepositService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SecurityDepositController extends Controller
{
    public function __construct(protected SecurityDepositService $service)
    {
        $this->middleware('permission:security-deposits-read')->only('index', 'filter', 'exportExcel', 'exportCsv');
        $this->middleware('permission:security-deposits-update')->only('approve', 'rejected');
    }

    public function index()
    {
        $security_deposits = $this->service->listForChannel(Channel::Admin, auth()->user(), ['per_page' => 10]);

        return view('admin.security-deposits.index', compact('security_deposits'));
    }

    public function file_download(string $id)
    {
        $security_deposit = SecurityDeposit::findOrFail($id);
        $this->authorize('fileDownload', $security_deposit);

        $filePath = $security_deposit->payment_data['payment_receipt'] ?? null;

        if (empty($filePath) || ! Storage::exists($filePath)) {
            return redirect()->back()->with('error', 'File not found');
        }

        return Storage::download($filePath);
    }

    public function approve(string $id)
    {
        $security_deposit = SecurityDeposit::findOrFail($id);
        $this->authorize('update', $security_deposit);

        $this->service->approve($security_deposit);

        return response()->json([
            'message' => __('Security Deposit Approved successfully'),
            'redirect' => route('admin.security-deposits.index'),
        ]);
    }

    public function rejected(Request $request, string $id)
    {
        $request->validate([
            'meta.reason' => 'required|string|max:255',
        ]);

        $security_deposit = SecurityDeposit::findOrFail($id);
        $this->authorize('update', $security_deposit);

        $this->service->reject($security_deposit, $request->input('meta.reason'));

        return response()->json([
            'message' => __('Security Deposit Rejected successfully'),
            'redirect' => route('admin.security-deposits.index'),
        ]);
    }

    // Restaurado (spec 5.17, Q14-a): contrato verbatim pre-4.2 — el branch
    // Admin del service no aplica search/status/date (inputs del form = no-op).
    public function filter(Request $request)
    {
        $security_deposits = $this->service->listForChannel(Channel::Admin, auth()->user(), ['per_page' => $request->per_page ?? 20]);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.security-deposits.datas', ['security_deposits' => $security_deposits])->render(),
            ]);
        }

        return redirect(url()->previous());
    }

    // Restaurado (spec 5.17): filename verbatim pre-4.2.
    public function exportExcel()
    {
        return Excel::download(new AdminSecurityDepositExport, 'security-deposits.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new AdminSecurityDepositExport, 'security-deposits.csv');
    }
}
