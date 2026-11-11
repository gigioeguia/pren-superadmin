<?php

namespace App\Http\Controllers\Landlord;

use App\Exports\LandlordRefundRequestExport;
use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AcnooRefundRequestController extends Controller
{
    use HasAcnooActions;

    public function index()
    {
        $refund_requests = RefundRequest::with(['property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'tenant:id,name', 'deposit:id,deposit_amount,utility_advance,rent_advance,total_amount'])->where('landlord_id', auth()->id())->latest()->paginate(10);

        return view('landlord.refund-request.index', compact('refund_requests'));
    }

    public function show(string $id)
    {

        $refund = RefundRequest::with(['property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'landlord:id,name,phone,email', 'landlord.user_details:user_id,address_info', 'tenant:id,name,phone,email', 'tenant.user_details:user_id,address_info', 'deposit:id,deposit_amount,utility_advance,rent_advance,total_amount'])->findOrFail($id);

        return view('landlord.refund-request.view', compact('refund'));
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return RefundRequest::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        $search = $request->input('search');

        return RefundRequest::with(['property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'tenant:id,name', 'deposit:id,deposit_amount,utility_advance,rent_advance,total_amount'])->where('landlord_id', auth()->id())->when($search, function ($q) use ($search) {
            $q->where(function ($q) use ($search) {
                $q->where('amount', 'like', '%'.$search.'%')
                    ->orWhereHas('tenant', function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('property', function ($q) use ($search) {
                        $q->where('puid', 'like', '%'.$search.'%');
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
        return 'landlord.refund-request.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['refund_requests' => $rows];
    }

    protected function acnooExport(Request $request): object
    {
        return new LandlordRefundRequestExport;
    }

    protected function acnooExcelFilename(): string
    {
        return 'refund-request';
    }

    protected function acnooCsvFilename(): string
    {
        return 'refund-request';
    }
}
