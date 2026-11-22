<?php

namespace App\Http\Controllers\Api;

use App\Enums\Channel;
use App\Exceptions\DuplicateApplicationException;
use App\Exceptions\RentPeriodTooShortException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Application\StoreApplicationRequest;
use App\Models\Application;
use App\Services\ApplicationService;
use Illuminate\Http\Request;

class TenantApplicationController extends Controller
{
    public function __construct(protected ApplicationService $applications) {}

    public function index(Request $request)
    {
        $data = $this->applications->listForChannel(Channel::Api, $request->user(), $request->only(['status', 'search', 'landlord_id', 'per_page']));

        return response()->json([
            'message' => __('Data fetched successfully.'),
            'data' => $data,
        ]);
    }

    public function store(StoreApplicationRequest $request)
    {
        try {
            $application = $this->applications->store($request->validated(), $request->user());
        } catch (DuplicateApplicationException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (RentPeriodTooShortException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => __('Application created successfully.'),
            'data' => $application,
        ]);
    }

    public function show(string $id)
    {
        $data = Application::findOrFail($id);
        $this->authorize('view', $data);
        $data = $this->applications->loadForShow($data, Channel::Api);

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $data,
        ]);
    }

    public function agreement_sign(Request $request, string $id)
    {
        $request->validate([
            'tenant_agreement' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'landlord_agreement' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        $application = Application::findOrFail($id);
        $this->authorize('agreementSign', $application);

        try {
            $this->applications->agreementSign($application, $request->file('tenant_agreement'));
        } catch (\Exception) {
            // Rollback ya ejecutado por el service; el catch \Exception
            // (no \Throwable) replica el alcance del controlador original.
            return response()->json([
                'message' => 'Agreement sign failed',
            ], 500);
        }

        return response()->json([
            'message' => 'Agreement signed successfully',
        ]);
    }
}
