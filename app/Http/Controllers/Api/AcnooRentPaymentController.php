<?php

namespace App\Http\Controllers\Api;

use App\Models\RentPayment;
use App\Http\Controllers\Controller;

class AcnooRentPaymentController extends Controller
{
    public function index()
    {
        $rent_payments = RentPayment::select('id', 'rent_id', 'invoice_no', 'charge', 'total_amount', 'subtotal_amount', 'payment_status', 'created_at')->with('rent:id,tenant_id,landlord_id,property_id', 'rent.tenant:id,name', 'rent.landlord:id,name', 'rent.property:id,puid', 'rent.property.propertyDetail:id,property_id,property_info')
            ->when(auth()->user()->role == 'landlord', function ($query) {
                $query->whereHas('rent', function ($query) {
                    $query->where('landlord_id', auth()->user()->id);
                });
            })
            ->when(auth()->user()->role == 'tenant', function ($query) {
                $query->whereHas('rent', function ($query) {
                    $query->where('tenant_id', auth()->user()->id);
                });
            })
            ->when(request('payment_status'), function ($query) {
                $query->where('payment_status', request('payment_status'));
            })
            ->when(request('from_date') && request('to_date'), function ($q) {
                $q->whereDate('created_at', '>=', request('from_date'));
                $q->whereDate('created_at', '<=', request('to_date'));
            })
            ->latest()
            ->paginate(10);

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $rent_payments,
        ]);
    }

    public function show(string $id)
    {
        $rent_payment = RentPayment::with(['rent:id,tenant_id,landlord_id,property_id', 'rent.tenant:id,name,email,phone', 'rent.tenant.user_details:user_id,address_info', 'rent.landlord:id,name,email,phone', 'rent.landlord.user_details:user_id,address_info', 'rent.property:id,puid', 'rent.property.propertyDetail:id,property_id,property_info'])->findOrFail($id);

        $errors = session()->get('errors', new \Illuminate\Support\ViewErrorBag);
        $html = view('api-invoice.rent-payment', compact('rent_payment', 'errors'))->render();


        return response()->json([
            'message' => 'Data fetched successfully.',
            'data' => [
                'id' => $rent_payment->id,
            ],
            'html' => $html
        ]);
    }
}
