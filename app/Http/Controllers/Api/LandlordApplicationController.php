<?php

namespace App\Http\Controllers\Api;

use App\Enums\Channel;
use App\Exceptions\ApplicationAgreementMissingException;
use App\Exceptions\ApplicationAgreementSignedException;
use App\Exceptions\ApplicationDepositUnpaidException;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\ApplicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LandlordApplicationController extends Controller
{
    public function __construct(protected ApplicationService $applications) {}

    public function processing(Request $request, string $id)
    {
        $request->validate([
            'landlord_agreement' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        $application = Application::findOrFail($id);
        $this->authorize('process', $application);

        $application = $this->applications->processing($application, $request->file('landlord_agreement'), Channel::Api);

        return response()->json([
            'message' => 'Application processes successfully',
            'data' => $application,
        ]);
    }

    public function approved(string $id)
    {
        $application = Application::findOrFail($id);
        $this->authorize('approve', $application);

        try {
            $this->applications->approve($application, Channel::Api);
        } catch (ApplicationAgreementMissingException $e) {
            // Quirk preservado (tabla §4): el canal api responde 200.
            return response()->json(['message' => $e->getMessage()]);
        } catch (ApplicationDepositUnpaidException) {
            return response()->json(['message' => 'Application security deposit unpaid'], 404);
        }

        return response()->json([
            'message' => 'Application approved successfully',
            'data' => $application,
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
            $application = $this->applications->reject($application, $request->input('rejected_cause'), Channel::Api);
        } catch (ApplicationAgreementSignedException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json([
            'message' => 'Application rejected successfully',
            'data' => $application,
        ]);
    }

    public function file_download(string $id)
    {
        $application = Application::findOrFail($id);
        $this->authorize('fileDownload', $application);

        $filePath = $this->applications->fileDownload($application, Channel::Api, null);

        if (is_null($filePath)) {
            return response()->json([
                'message' => 'File not found',
            ]);
        }

        if (Storage::exists($filePath)) {
            return Storage::download($filePath);
        }
    }
}
