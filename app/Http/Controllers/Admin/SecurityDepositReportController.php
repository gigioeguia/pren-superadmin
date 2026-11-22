<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminSecurityDepositReportExport;
use App\Http\Controllers\Controller;
use App\Models\SecurityDeposit;
use App\Traits\HasTableActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SecurityDepositReportController extends Controller
{
    use HasTableActions;

    public function __construct()
    {
        $this->middleware('permission:reports-read')->only('index', 'show', 'filter', 'exportExcel', 'exportCsv');
    }

    public function index()
    {
        $security_deposits = SecurityDeposit::with(['tenant:id,name', 'property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'gateway:id,name'])->latest()->paginate(10);

        return view('admin.reports.security-deposits.index', compact('security_deposits'));
    }

    public function show(string $id)
    {
        $security_deposit = SecurityDeposit::with(['landlord:id,name,phone,email', 'landlord.user_details:user_id,address_info',  'tenant:id,name,phone,email', 'tenant.user_details:user_id,address_info'])->findOrFail($id);

        return view('landlord.security-deposits.view', compact('security_deposit'));
    }

    protected function model(): string
    {
        return SecurityDeposit::class;
    }

    protected function query(Request $request): Builder
    {
        return SecurityDeposit::with(['tenant:id,name', 'property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'gateway:id,name'])
            ->when($request->input('search'), function ($q) use ($request) {
                $q->where('invoice_no', 'like', '%'.$request->input('search').'%')
                    ->orWhere('status', 'like', '%'.$request->input('search').'%')
                    ->orWhere('total_amount', 'like', '%'.$request->input('search').'%')
                    ->orWhere('utility_advance', 'like', '%'.$request->input('search').'%');

                $q->orWhereHas('tenant', function ($q) use ($request) {
                    $q->where('name', 'like', '%'.$request->input('search').'%');
                });

                $q->orWhereHas('gateway', function ($q) use ($request) {
                    $q->where('name', 'like', '%'.$request->input('search').'%');
                });

                $q->orWhereHas('property.propertyDetail', function ($q) use ($request) {
                    $q->where('property_info', 'like', '%'.$request->input('search').'%');
                });
            })
            ->latest();
    }

    protected function view(): string
    {
        return 'admin.reports.security-deposits.datas';
    }

    protected function viewData(LengthAwarePaginator $rows): array
    {
        return ['security_deposits' => $rows];
    }

    protected function perPage(): int
    {
        return 20;
    }

    protected function export(Request $request): object
    {
        return new AdminSecurityDepositReportExport;
    }

    protected function excelFilename(): string
    {
        return 'security-deposit-reports';
    }

    protected function csvFilename(): string
    {
        return 'security-deposit-reports';
    }
}
