<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\Channel;
use App\Exceptions\RentNotActiveException;
use App\Exceptions\RentPeriodTooShortException;
use App\Exceptions\SecurityDepositMissingException;
use App\Exceptions\SecurityDepositUnpaidException;
use App\Exports\LandlordRentInvitationExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rent\StoreRentRequest;
use App\Models\Rent;
use App\Services\RentService;
use App\Support\ErrorMessage;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RentInvitationController extends Controller
{
    public function __construct(protected RentService $rents) {}

    public function index()
    {
        $dropdowns = $this->rents->indexDropdowns(auth()->id());
        $tenants = $dropdowns['tenants'];
        $properties = $dropdowns['properties'];
        $rents = $this->rents->listForChannel(Channel::Landlord, auth()->user());

        return view('landlord.rents.index', compact('rents', 'tenants', 'properties'));
    }

    public function getUnit(Request $request)
    {
        return response()->json(
            $this->rents->unitOptions((int) $request->input('property_id'))
        );
    }

    public function store(StoreRentRequest $request)
    {
        $data = $request->validated();
        $data['landlord_id'] = auth()->id();

        try {
            $this->rents->createWithDeposit($data, $request->file('landlord_agreement'));
        } catch (RentPeriodTooShortException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            report($e);

            return response()->json(['message' => 'Something went wrong'], 500);
        }

        return response()->json([
            'message' => 'Rent Invitation sent successfully',
            'redirect' => route('landlord.rents.index'),
        ]);
    }

    public function show(string $id)
    {
        $rent = $this->rents->loadForShow(Rent::findOrFail($id), Channel::Landlord);

        return view('landlord.rents.view', compact('rent'));
    }

    public function approved(string $id)
    {
        try {
            $this->rents->approve(Rent::findOrFail($id), Channel::Landlord);
        } catch (SecurityDepositMissingException) {
            return response()->json(['message' => 'Security Deposit not found.'], 404);
        } catch (SecurityDepositUnpaidException) {
            return response()->json(['message' => 'Security Deposit is unpaid.'], 400);
        } catch (\Exception $e) {
            report($e);

            return response()->json(['message' => __(ErrorMessage::GENERIC), 'error' => __(ErrorMessage::GENERIC)], 500);
        }

        // Divergencia por canal (spec §4): la rama web no notifica.
        return response()->json([
            'message' => 'Rent approved successfully',
            'redirect' => route('landlord.rents.index'),
        ]);
    }

    public function canceled(Request $request, string $id)
    {
        $request->validate([
            'title' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        try {
            $this->rents->cancel(
                Rent::findOrFail($id),
                Channel::Landlord,
                $request->input('title'),
                $request->input('reason')
            );
        } catch (RentNotActiveException) {
            return response()->json(['message' => 'Rent not found'], 404);
        }

        return response()->json([
            'message' => 'Rent cancelled successfully',
            'redirect' => route('landlord.rents.index'),
        ]);
    }

    /**
     * Antes servido por el trait de tablas; ahora delega al service
     * (spec §5.4) y renderiza la misma vista de filas.
     */
    public function filter(Request $request)
    {
        $rows = $this->rents->filterForLandlord(auth()->id(), $request->only(['search', 'per_page']));

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.rents.datas', ['rents' => $rows])->render(),
            ]);
        }

        return redirect(url()->previous());
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new LandlordRentInvitationExport, 'landlord-rent-invitation.xlsx');
    }

    public function exportCsv(Request $request)
    {
        return Excel::download(new LandlordRentInvitationExport, 'landlord-rent-invitation.csv');
    }
}
