<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminRentPaymentReportExport;
use App\Models\RentPayment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class AcnooRentPaymentReportController extends Controller
{
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

    public function acnooFilter(Request $request)
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
                $q->where('invoice_no', 'like', '%' . $request->search . '%')
                    ->orWhere('payment_status', 'like', '%' . $request->search . '%')
                    ->orWhere('utility_bill', 'like', '%' . $request->search . '%')
                    ->orWhere('monthly_rent', 'like', '%' . $request->search . '%')
                    ->orWhereHas('rent.tenant', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('rent.property.propertyDetail', function ($q) use ($request) {
                        $q->where('property_info->property_title', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('gateway', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $perPage = $request->input('per_page', 20);
        $rent_payments = $rent_paymentQuery->latest()->paginate($perPage);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.reports.rent-payment.datas', compact('rent_payments'))->render()
            ]);
        }

        return redirect(url()->previous());
    }

    public function show(string $id)
    {
        $rent_payment = RentPayment::with(['rent:id,landlord_id,tenant_id,property_id', 'rent.tenant:id,name,phone,email', 'rent.tenant.user_details:id,user_id,address_info', 'rent.landlord:id,name,phone,email', 'rent.landlord.user_details:id,user_id,address_info', 'rent.property:id,puid', 'rent.property.propertyDetail:id,property_id,property_info'])->findOrFail($id);
        return view('landlord.rent-payments.view', compact('rent_payment'));
    }

    public function exportExcel()
    {
        return Excel::download(new AdminRentPaymentReportExport, 'rent-payemnt-report.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new AdminRentPaymentReportExport, 'rent-payemnt-report.csv');
    }
}
