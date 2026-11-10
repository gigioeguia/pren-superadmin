<?php

namespace App\Http\Controllers\Api;

use App\Models\Application;
use App\Helpers\HasUploader;
use Illuminate\Http\Request;
use App\Models\SecurityDeposit;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class AcnooLandlordApplicationController extends Controller
{
    use HasUploader;

    public function processing(Request $request, string $id)
    {

        $application_process = Application::findOrFail($id);

        if ($application_process) {
            $application_process->update([
                'landlord_agreement' => $request->landlord_agreement ? $this->upload($request, 'landlord_agreement') : NULL,
                'status' => 1,
            ]);

            sendNotification($application_process->id, notify_users([$application_process->tenant_id, $application_process->property->landlord_id]), landlord_url: route('landlord.tenant-applications.index', ['id' => $application_process->id]),  landlord_msg: __('Your application has been processed successfully.'), tenant_msg: __('Your application has been processed.'));

            return response()->json([
                'message' => 'Application processes successfully',
                'data' =>  $application_process
            ]);
        } else {
            return response()->json(['message' => 'Application not found'], 404);
        }
    }

    public function approved(string $id)
    {

        $approve_application = Application::findOrFail($id);
        $security_deposit = SecurityDeposit::where('property_id', $approve_application->property_id)
                                            ->where('unit_id', $approve_application->unit_id)
                                            ->first();
        if (!empty($approve_application->tenant_agreement) && Storage::exists($approve_application->tenant_agreement)) {
            if ($security_deposit->status == 'paid') {
                $approve_application->update([
                    'status' => 2,
                ]);

                $security_deposit->rent()->update([
                    'status' => 'active'
                ]);

                sendNotification($approve_application->id, notify_users([$approve_application->tenant_id, $approve_application->property->landlord_id]), landlord_url: route('landlord.tenant-applications.index', ['id' => $approve_application->id]),  landlord_msg: __('The application has been approved successfully.'), tenant_msg: __('Congratulations! Your application has been approved.'));

                return response()->json([
                    'message' => 'Application approved successfully',
                    'data' =>  $approve_application
                ]);
            } else {
                return response()->json(['message' => 'Application security deposit unpaid'], 404);
            }
        } else {
            return response()->json([
                'message' => 'Tenant agreement file does not exist',
            ]);
        }
    }

    public function rejected(Request $request, string $id)
    {
        $request->validate([
            'rejected_cause' => 'required|string|max:255',
        ]);

        $application_reject = Application::findOrFail($id);

        if (!empty($application_reject->tenant_agreement) && Storage::exists($application_reject->tenant_agreement)) {
            return response()->json([
                'message' => 'Application cannot be rejected because a tenant agreement already exists.',
            ], 409);
        }

        if ($application_reject) {
            $application_reject->update([
                'rejected_cause' => $request->rejected_cause,
                'status' => 3
            ]);

            sendNotification(
                $application_reject->id, notify_users([$application_reject->tenant_id, $application_reject->property->landlord_id]),
                landlord_url: route('landlord.tenant-applications.index', ['id' => $application_reject->id]),
                landlord_msg: __('The application has been rejected.'),
                tenant_msg: __('We regret to inform you that your application has been rejected.')
            );

            return response()->json([
                'message' => 'Application rejected successfully',
                'data' =>  $application_reject
            ]);
        } else {
            return response()->json(['message' => 'Application not found'], 404);
        }
    }

    public function file_download(string $id)
    {
        $application = Application::findOrFail($id);

        if (is_null($application->file)) {
            return response()->json([
                'message' => 'File not found',
            ]);
        }

        $filePath = $application->file;

        if (Storage::exists($filePath)) {
            return Storage::download($filePath);
        }
    }
}
