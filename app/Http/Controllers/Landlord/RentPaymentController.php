<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\Channel;
use App\Exports\LandlordRentPaymentExport;
use App\Http\Controllers\Controller;
use App\Models\RentPayment;
use App\Services\RentPaymentService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RentPaymentController extends Controller
{
    public function __construct(protected RentPaymentService $service) {}

    public function index()
    {
        $rent_payments = $this->service->listForChannel(Channel::Landlord, auth()->user(), ['per_page' => 10]);

        return view('landlord.rent-payments.index', compact('rent_payments'));
    }

    public function show(string $id)
    {
        $rent_payment = RentPayment::with(['rent:id,landlord_id,tenant_id,property_id', 'rent.tenant:id,name,phone,email', 'rent.tenant.user_details:id,user_id,address_info', 'rent.landlord:id,name,phone,email', 'rent.landlord.user_details:id,user_id,address_info', 'rent.property:id,puid', 'rent.property.propertyDetail:id,property_id,property_info'])->findOrFail($id);
        $this->authorize('view', $rent_payment);

        return view('landlord.rent-payments.view', compact('rent_payment'));
    }

    public function filter(Request $request)
    {
        $rent_payments = $this->service->listForChannel(Channel::Landlord, auth()->user(), array_merge($request->all(), ['per_page' => $request->per_page ?? 20]));

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.rent-payments.datas', ['rent_payments' => $rent_payments])->render(),
            ]);
        }

        return redirect(url()->previous());
    }

    /**
     * Rutas restauradas de 4.2 (spec residuales 5.13): filenames verbatim
     * de los hooks de export pre-4.2.
     */
    public function exportExcel(Request $request)
    {
        return Excel::download(new LandlordRentPaymentExport, 'landlord-rent-payments.xlsx');
    }

    public function exportCsv(Request $request)
    {
        return Excel::download(new LandlordRentPaymentExport, 'landlord-rent-payments.csv');
    }
}
