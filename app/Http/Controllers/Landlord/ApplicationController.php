<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\Channel;
use App\Exceptions\ApplicationAgreementMissingException;
use App\Exceptions\ApplicationAgreementSignedException;
use App\Exceptions\ApplicationDepositUnpaidException;
use App\Exports\LandlordApplicationExport;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\ApplicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ApplicationController extends Controller
{
    public function __construct(protected ApplicationService $applications) {}

    public function index()
    {
        $applications = $this->applications->listForChannel(Channel::Landlord, auth()->user());

        return view('landlord.application.index', compact('applications'));
    }

    public function show(string $id)
    {
        $application = Application::findOrFail($id);
        $this->authorize('view', $application);
        $application = $this->applications->loadForShow($application, Channel::Landlord);

        return view('landlord.application.view', compact('application'));
    }

    public function processing(Request $request, string $id)
    {
        // Orden preservado del canal web: findOrFail → authorize → validate.
        $application = Application::findOrFail($id);
        $this->authorize('process', $application);

        $request->validate([
            'landlord_agreement' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        $this->applications->processing($application, $request->file('landlord_agreement'), Channel::Landlord);

        return response()->json([
            'message' => 'Application processes successfully',
            'redirect' => route('landlord.tenant-applications.show', $application->id),
        ]);
    }

    public function approved(string $id)
    {
        $application = Application::findOrFail($id);
        $this->authorize('approve', $application);

        try {
            $this->applications->approve($application, Channel::Landlord);
        } catch (ApplicationAgreementMissingException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (ApplicationDepositUnpaidException) {
            return response()->json(['message' => 'Application Unpaid'], 404);
        }

        return response()->json([
            'message' => 'Application approved successfully',
            'redirect' => route('landlord.tenant-applications.show', $application->id),
        ]);
    }

    public function rejected(Request $request, string $id)
    {
        $request->validate([
            'rejected_cause' => 'required|string|max:255',
        ]);

        $application = Application::findOrFail($id);
        $this->authorize('reject', $application);

        try {
            $this->applications->reject($application, $request->input('rejected_cause'), Channel::Landlord);
        } catch (ApplicationAgreementSignedException $e) {
            // Quirk preservado (tabla §4): el canal web responde 200.
            return response()->json(['message' => $e->getMessage()]);
        }

        return response()->json([
            'message' => 'Application rejected successfully',
            'redirect' => route('landlord.tenant-applications.show', $application->id),
        ]);
    }

    public function file_download(string $id, string $type)
    {
        $application = Application::findOrFail($id);
        $this->authorize('fileDownload', $application);

        $filePath = $this->applications->fileDownload($application, Channel::Landlord, $type);

        if (is_null($filePath)) {
            return response()->json([
                'message' => 'File not found',
            ]);
        }

        if (Storage::exists($filePath)) {
            return Storage::download($filePath);
        }
    }

    /**
     * Antes servido por HasTableActions; ahora delega al service
     * (spec §5.4) y renderiza la misma vista de filas.
     */
    public function filter(Request $request)
    {
        $rows = $this->applications->filterForLandlord(auth()->id(), $request->only(['search', 'per_page']));

        if ($request->ajax()) {
            return response()->json([
                'data' => view('landlord.application.datas', ['applications' => $rows])->render(),
            ]);
        }

        return redirect(url()->previous());
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new LandlordApplicationExport, 'landlord-applications.xlsx');
    }

    public function exportCsv(Request $request)
    {
        return Excel::download(new LandlordApplicationExport, 'landlord-applications.csv');
    }
}
