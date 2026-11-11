<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminRentPaymentReportExport;
use App\Http\Controllers\Controller;
use App\Models\RentPayment;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AcnooRentPaymentReportController extends Controller
{
    use HasAcnooActions;

    public function __construct()
    {
        $this->middleware('permission:reports-read')->only('index', 'show');
    }

    public function index()
    {
        $rent_payments = RentPayment::with(['rent:id,tenant_id,property_id', 'rent.tenant:id,name', 'rent.property:id,puid', 'rent.property.propertyDetail:id,property_id,property_info', 'gateway:id,name'])
            ->latest()
            ->paginate(10);

        return view('admin.reports.rent-payment.index', compact('rent_payments'));
    }

    public function show(string $id)
    {
        $rent_payment = RentPayment::with(['rent:id,landlord_id,tenant_id,property_id', 'rent.tenant:id,name,phone,email', 'rent.tenant.user_details:id,user_id,address_info', 'rent.landlord:id,name,phone,email', 'rent.landlord.user_details:id,user_id,address_info', 'rent.property:id,puid', 'rent.property.propertyDetail:id,property_id,property_info'])->findOrFail($id);

        return view('landlord.rent-payments.view', compact('rent_payment'));
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return RentPayment::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        $rent_paymentQuery = RentPayment::with(['rent:id,tenant_id,property_id', 'rent.tenant:id,name', 'rent.property:id,puid', 'rent.property.propertyDetail:id,property_id,property_info', 'gateway:id,name']);

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $startDate = Carbon::parse($request->from_date)->format('Y-m-d');
            $endDate = Carbon::parse($request->to_date)->format('Y-m-d');

            $rent_paymentQuery->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        }

        if ($request->filled('search')) {
            $rent_paymentQuery->where(function ($q) use ($request) {
                $q->where('invoice_no', 'like', '%'.$request->search.'%')
                    ->orWhere('payment_status', 'like', '%'.$request->search.'%')
                    ->orWhere('utility_bill', 'like', '%'.$request->search.'%')
                    ->orWhere('monthly_rent', 'like', '%'.$request->search.'%')
                    ->orWhereHas('rent.tenant', function ($q) use ($request) {
                        $q->where('name', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('rent.property.propertyDetail', function ($q) use ($request) {
                        $q->where('property_info->property_title', 'like', '%'.$request->search.'%');
                    })
                    ->orWhereHas('gateway', function ($q) use ($request) {
                        $q->where('name', 'like', '%'.$request->search.'%');
                    });
            });
        }

        return $rent_paymentQuery->latest();
    }

    protected function acnooView(): string
    {
        return 'admin.reports.rent-payment.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['rent_payments' => $rows];
    }

    protected function acnooPerPage(): int
    {
        return 20;
    }

    protected function acnooExport(Request $request): object
    {
        return new AdminRentPaymentReportExport;
    }

    protected function acnooExcelFilename(): string
    {
        return 'rent-payemnt-report';
    }

    protected function acnooCsvFilename(): string
    {
        return 'rent-payemnt-report';
    }
}
