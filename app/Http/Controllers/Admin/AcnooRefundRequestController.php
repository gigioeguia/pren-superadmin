<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminRefundRequestReportExport;
use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AcnooRefundRequestController extends Controller
{
    use HasAcnooActions;

    public function __construct()
    {
        $this->middleware('permission:reports-read')->only('index', 'show');
    }

    public function index()
    {
        $refund_requests = RefundRequest::with(['property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'landlord:id,name', 'tenant:id,name', 'deposit:id,total_amount'])->latest()->paginate(10);

        return view('admin.reports.refund-request.index', compact('refund_requests'));
    }

    public function show(string $id)
    {
        $refund = RefundRequest::with(
            [
                'property:id,puid',
                'property.propertyDetail:id,property_id,property_info',
                'landlord:id,name,phone,email',
                'landlord.user_details:user_id,address_info',
                'tenant:id,name,phone,email',
                'tenant.user_details:user_id,address_info',
                'deposit:id,deposit_amount,utility_advance,rent_advance,total_amount',
            ]
        )->findOrFail($id);

        return view('landlord.refund-request.view', compact('refund'));
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return RefundRequest::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        $refund_requestQuery = RefundRequest::with(['property:id,puid', 'property.propertyDetail:id,property_id,property_info', 'landlord:id,name', 'tenant:id,name', 'deposit:id,total_amount']);

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $startDate = Carbon::parse($request->from_date)->format('Y-m-d');
            $endDate = Carbon::parse($request->to_date)->format('Y-m-d');

            $refund_requestQuery->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        }

        if ($request->filled('search')) {
            $refund_requestQuery->where(function ($q) use ($request) {
                $q->where('status', 'like', '%'.$request->search.'%')
                    ->orWhereHas('property', function ($q) use ($request) {
                        $q->where('puid', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('property.propertyDetail', function ($q) use ($request) {
                        $q->where('property_info', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('landlord', function ($q) use ($request) {
                        $q->where('name', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('tenant', function ($q) use ($request) {
                        $q->where('name', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('deposit', function ($q) use ($request) {
                        $q->where('total_amount', 'like', '%'.$request->search.'%');
                    });
            });
        }

        return $refund_requestQuery->latest();
    }

    protected function acnooView(): string
    {
        return 'admin.reports.refund-request.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['refund_requests' => $rows];
    }

    protected function acnooPerPage(): int
    {
        return 20;
    }

    protected function acnooExport(Request $request): object
    {
        return new AdminRefundRequestReportExport;
    }

    protected function acnooExcelFilename(): string
    {
        return 'refund-reports';
    }

    protected function acnooCsvFilename(): string
    {
        return 'refund-reports';
    }
}
