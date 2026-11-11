<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminSecurityDepositReportExport;
use App\Http\Controllers\Controller;
use App\Models\SecurityDeposit;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AcnooSecurityDepositReportController extends Controller
{
    use HasAcnooActions;

    public function __construct()
    {
        $this->middleware('permission:reports-read')->only('index', 'show');
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

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return SecurityDeposit::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        return SecurityDeposit::with(['tenant:id,name', 'property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'gateway:id,name'])
            ->when(request('search'), function ($q) {
                $q->where('invoice_no', 'like', '%'.request('search').'%')
                    ->orWhere('status', 'like', '%'.request('search').'%')
                    ->orWhere('total_amount', 'like', '%'.request('search').'%')
                    ->orWhere('utility_advance', 'like', '%'.request('search').'%');

                $q->orWhereHas('tenant', function ($q) {
                    $q->where('name', 'like', '%'.request('search').'%');
                });

                $q->orWhereHas('gateway', function ($q) {
                    $q->where('name', 'like', '%'.request('search').'%');
                });

                $q->orWhereHas('property.propertyDetail', function ($q) {
                    $q->where('property_info', 'like', '%'.request('search').'%');
                });
            })
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'admin.reports.security-deposits.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['security_deposits' => $rows];
    }

    protected function acnooPerPage(): int
    {
        return 20;
    }

    protected function acnooExport(Request $request): object
    {
        return new AdminSecurityDepositReportExport;
    }

    protected function acnooExcelFilename(): string
    {
        return 'security-deposit-reports';
    }

    protected function acnooCsvFilename(): string
    {
        return 'security-deposit-reports';
    }
}
