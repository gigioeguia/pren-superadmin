<?php

namespace App\Http\Controllers\Api;

use App\Enums\Channel;
use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Gateway;
use App\Models\SecurityDeposit;
use App\Models\User;
use App\Services\SecurityDepositService;
use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag;

class SecurityDepositController extends Controller
{
    use HasUploader;

    public function __construct(protected SecurityDepositService $service) {}

    public function index(Request $request)
    {
        $security_deposit = $this->service->listForChannel(Channel::Api, $request->user(), $request->all());

        return response()->json([
            'message' => __('Data fetched successfully.'),
            'data' => $security_deposit,
        ]);
    }

    public function show(string $id)
    {
        $security_deposit = SecurityDeposit::with(['property:id,security_deposit,utility_deposit,rent_info', 'property.propertyDetail:property_id,property_info', 'landlord:id,name,phone,email', 'landlord.user_details:user_id,address_info', 'tenant:id,name,phone,email', 'tenant.user_details:user_id,address_info'])->findOrFail($id);
        $this->authorize('view', $security_deposit);

        $errors = session()->get('errors', new ViewErrorBag);
        $html = view('api-invoice.security-deposit', compact('security_deposit', 'errors'))->render();

        return response()->json([
            'message' => 'Data fetched successfully.',
            'data' => [
                'id' => $security_deposit->id,
                'total_amount' => $security_deposit->total_amount,
            ],
            'html' => $html,
        ]);
    }

    public function offline_payment(Request $request, string $gateway_id, string $id)
    {
        $gateway = Gateway::findOrFail($gateway_id);

        if ($gateway->is_manual == 1) {
            $request->validate([
                'payment_receipt' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
                'payment_note' => 'nullable|string',
            ]);

            // Upload antes de abrir la transacción; se calcula una sola vez (Fase 4 / 4.8)
            $receiptPath = $request->payment_receipt ? $this->upload($request, 'payment_receipt') : null;

            // Tipado explícito: auth()->user() es union de guards con modelos
            // fantasma en config (spec 5.11 — writes sobre User tipado).
            $tenant = User::findOrFail(auth()->id());

            try {
                // El authorize corre dentro del try como en el original: un
                // depósito ajeno mantiene el 500 'Payment failed' congelado (F13b)
                if ($request->payment_type == 'security_deposit') {
                    $this->authorize('offlinePayment', SecurityDeposit::findOrFail($id));
                }

                $this->service->offlinePayment($tenant, [
                    'payment_type' => $request->payment_type,
                    'id' => $id,
                    'gateway_id' => $gateway->id,
                    'receipt_path' => $receiptPath,
                    'payment_note' => $request->payment_note,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Payment failed',
                ], 500);
            }

            if ($request->payment_type == 'subscription') {
                session()->forget('user_id');

                return response()->json([
                    'message' => 'New subscription purchased requested.',
                ]);
            }

            return response()->json([
                'message' => 'Payment sent successfully',
            ]);
        }

        return response()->json([
            'message' => 'Invalid payment gateway',
        ], 400);
    }
}
