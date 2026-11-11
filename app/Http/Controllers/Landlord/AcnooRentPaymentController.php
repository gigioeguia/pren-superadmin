<?php

namespace App\Http\Controllers\Landlord;

use App\Exports\LandlordRentPaymentExport;
use App\Http\Controllers\Controller;
use App\Models\RentPayment;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AcnooRentPaymentController extends Controller
{
    public function index()
    {
        $landlord_id = auth()->id();

        $rent_payments = RentPayment::with(['rent:id,tenant_id,property_id,landlord_id', 'rent.tenant:id,name', 'rent.property:id,puid', 'rent.property.propertyDetail:id,property_id,property_info'])
            ->whereHas('rent', function ($query) use ($landlord_id) {
                $query->where('landlord_id', $landlord_id);
            })
            ->latest()
            ->paginate(10);

        return view('landlord.rent-payments.index', compact('rent_payments'));
    }

    public function acnooFilter(Request $request)
    {
        $landlord_id = auth()->id();

        $rent_payments = RentPayment::with(['rent:id,tenant_id,property_id,landlord_id', 'rent.tenant:id,name', 'rent.property:id,puid', 'rent.property.propertyDetail:id,property_id,property_info'])
            ->whereHas('rent', function ($query) use ($landlord_id) {
                $query->where('landlord_id', $landlord_id);
            })
            ->when(request('search'), function ($q) {
                $q->where(function ($q) {
                    $search = request('search');
                    $q->where('payment_date', 'like', '%'.$search.'%')
                        ->orwhere('total_amount', 'like', '%'.$search.'%')
                        ->orwhere('invoice_no', 'like', '%'.$search.'%')
                        ->orWhereHas('rent.tenant', function ($query) use ($search) {
                            $query->where('name', 'like', '%'.$search.'%');
                        })

                        ->orWhereHas('rent.property', function ($query) use ($search) {
                            $query->where('puid', 'like', '%'.$search.'%');
                        })

                        ->orWhereHas('rent.property.propertyDetail', function ($query) use ($search) {
                            $query->where('property_info->property_title', 'like', '%'.$search.'%');
                        });
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 20);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.rent-payments.datas', compact('rent_payments'))->render(),
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
        return Excel::download(new LandlordRentPaymentExport, 'landlord-rent-payments.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new LandlordRentPaymentExport, 'landlord-rent-payments.csv');
    }
}
