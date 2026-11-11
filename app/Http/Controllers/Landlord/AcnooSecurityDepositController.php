<?php

namespace App\Http\Controllers\Landlord;

use App\Exports\LandlordSecurityDepositExport;
use App\Http\Controllers\Controller;
use App\Models\SecurityDeposit;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AcnooSecurityDepositController extends Controller
{
    use HasAcnooActions;

    public function index()
    {
        $security_deposits = SecurityDeposit::with(['tenant:id,name', 'property:id,puid',  'property.propertyDetail:id,property_id,property_info'])->where('landlord_id', auth()->id())->latest()->paginate(10);

        return view('landlord.security-deposits.index', compact('security_deposits'));
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
        return SecurityDeposit::with(['tenant:id,name', 'property:id,puid',  'property.propertyDetail:id,property_id,property_info'])
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
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'landlord.security-deposits.datas';
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
        return new LandlordSecurityDepositExport;
    }

    protected function acnooExcelFilename(): string
    {
        return 'security-deposits';
    }

    protected function acnooCsvFilename(): string
    {
        return 'security-deposits';
    }
}
