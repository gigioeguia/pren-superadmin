<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Models\RentPayment;
use App\Services\RentPaymentService;
use Illuminate\Http\Request;

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

        return view('landlord.rent-payments.datas', ['rent_payments' => $rent_payments]);
    }
}
