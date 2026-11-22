<?php

namespace App\Http\Controllers\Api;

use App\Enums\Channel;
use App\Exceptions\RentNotActiveException;
use App\Exceptions\RentPeriodTooShortException;
use App\Exceptions\SecurityDepositUnpaidException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rent\StoreRentRequest;
use App\Models\Rent;
use App\Services\RentService;
use Illuminate\Http\Request;

class RentController extends Controller
{
    public function __construct(protected RentService $rents) {}

    public function index(Request $request)
    {
        $actor = $request->user();

        // Comparación laxa preservada del controlador original: un valor
        // truthy no booleano (p.ej. "false") también entra a la rama.
        if ($request->has('no_paginate') && $request->input('no_paginate') == true) {
            $responseData = ['data' => $this->rents->listAll($actor, $request->only(['status']))];
        } else {
            $responseData = $this->rents->listForChannel(Channel::Api, $actor, $request->only(['landlord_id', 'status', 'per_page']));
        }

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $responseData,
        ]);
    }

    public function store(StoreRentRequest $request)
    {
        $data = $request->validated();
        $data['landlord_id'] = auth()->id();

        try {
            $rent = $this->rents->createWithDeposit($data, $request->file('landlord_agreement'));
        } catch (RentPeriodTooShortException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Exception) {
            // Sin report() — comportamiento original del canal api (spec §4).
            return response()->json(['message' => 'Something went wrong'], 500);
        }

        return response()->json([
            'message' => 'Data save successfully',
            'data' => $rent,
        ]);
    }

    public function show(string $id)
    {
        $data = $this->rents->loadForShow(Rent::findOrFail($id), Channel::Api);

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $data,
        ]);
    }

    public function tenant_agreement(Request $request, string $id)
    {
        $request->validate([
            'tenant_agreement' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        $rent = $this->rents->agreementUpload(Rent::findOrFail($id), $request->file('tenant_agreement'));

        return response()->json([
            'message' => 'Agreement sent successfully',
            'data' => $rent,
        ]);
    }

    public function tenant_accept(string $id)
    {
        $rent = $this->rents->tenantAccept(Rent::findOrFail($id));

        return response()->json([
            'message' => 'Rent accepted successfully',
            'status' => $rent,
        ]);
    }

    public function approved(string $id)
    {
        try {
            $rent = $this->rents->approve(Rent::findOrFail($id), Channel::Api);
        } catch (SecurityDepositUnpaidException) {
            return response()->json(['message' => 'Rent not found'], 404);
        }

        // Quirk preservado (spec §4): depósito ausente no se captura aquí —
        // la excepción sube y responde 500 igual que el controlador original.
        return response()->json([
            'message' => 'Rent approved successfully',
            'status' => $rent,
        ]);
    }

    public function tenant_cancelled(Request $request, string $id)
    {
        $request->validate([
            'title' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        try {
            $rent = $this->rents->cancel(
                Rent::findOrFail($id),
                Channel::Api,
                $request->input('title'),
                $request->input('reason')
            );
        } catch (RentNotActiveException) {
            return response()->json(['message' => 'Rent not found'], 404);
        }

        return response()->json([
            'message' => 'Rent cancelled successfully',
            'data' => $rent,
        ]);
    }
}
