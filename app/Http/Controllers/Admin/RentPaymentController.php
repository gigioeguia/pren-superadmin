<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Models\RentPayment;
use App\Services\RentPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RentPaymentController extends Controller
{
    public function __construct(protected RentPaymentService $service)
    {
        $this->middleware('permission:rent-payments-read')->only('index');
        $this->middleware('permission:rent-payments-update')->only('approve', 'rejected');
    }

    public function index()
    {
        $rent_payments = $this->service->listForChannel(Channel::Admin, auth()->user(), ['per_page' => 10]);

        return view('admin.rent-payment.index', compact('rent_payments'));
    }

    public function file_download(string $id)
    {
        $rent_payment = RentPayment::findOrFail($id);
        $this->authorize('fileDownload', $rent_payment);

        $filePath = $rent_payment->payment_data['payment_receipt'] ?? null;

        if (empty($filePath) || ! Storage::exists($filePath)) {
            return redirect()->back()->with('error', 'File not found');
        }

        return Storage::download($filePath);
    }

    public function approve(string $id)
    {
        $rent_payment = RentPayment::findOrFail($id);
        $this->authorize('update', $rent_payment);

        $this->service->approve($rent_payment);

        return response()->json([
            'message' => __('Rent Payment Approved successfully'),
            'redirect' => route('admin.rent-payments.index'),
        ]);
    }

    public function rejected(Request $request, string $id)
    {
        $request->validate([
            'meta.reason' => 'required|string|max:255',
        ]);

        $rent_payment = RentPayment::findOrFail($id);
        $this->authorize('update', $rent_payment);

        $this->service->reject($rent_payment, $request->input('meta.reason'));

        return response()->json([
            'message' => __('Rent Payment Rejected successfully'),
            'redirect' => route('admin.rent-payments.index'),
        ]);
    }
}
