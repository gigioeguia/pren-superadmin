<?php

namespace App\Http\Controllers\Api;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Models\RentPayment;
use App\Services\RentPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag;

class RentPaymentController extends Controller
{
    public function __construct(protected RentPaymentService $service) {}

    public function index(Request $request)
    {
        $rent_payments = $this->service->listForChannel(Channel::Api, $request->user(), $request->all());

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $rent_payments,
        ]);
    }

    public function show(string $id)
    {
        $rent_payment = RentPayment::with(['rent:id,tenant_id,landlord_id,property_id', 'rent.tenant:id,name,email,phone', 'rent.tenant.user_details:user_id,address_info', 'rent.landlord:id,name,email,phone', 'rent.landlord.user_details:user_id,address_info', 'rent.property:id,puid', 'rent.property.propertyDetail:id,property_id,property_info'])->findOrFail($id);
        $this->authorize('view', $rent_payment);

        $errors = session()->get('errors', new ViewErrorBag);
        $html = view('api-invoice.rent-payment', compact('rent_payment', 'errors'))->render();

        return response()->json([
            'message' => 'Data fetched successfully.',
            'data' => [
                'id' => $rent_payment->id,
            ],
            'html' => $html,
        ]);
    }
}
