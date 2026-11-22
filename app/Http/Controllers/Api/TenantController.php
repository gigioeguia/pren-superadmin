<?php

namespace App\Http\Controllers\Api;

use App\Enums\Channel;
use App\Exceptions\PlanLimitExceededException;
use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantRequest;
use App\Http\Requests\Tenant\UpdateTenantRequest;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    use HasUploader;

    public function __construct(protected TenantService $service) {}

    public function index(Request $request)
    {
        $landlordId = $request->user()->id;
        $filters = $request->all();
        if ($request->has('no_paginate') && $request->no_paginate) {
            $filters['per_page'] = 100000;
        }
        $tenants = $this->service->listForChannel(Channel::Api, $request->user(), $filters);

        if ($request->has('no_paginate') && $request->no_paginate) {
            $responseData = [
                'data' => $tenants->items(),
            ];
        } else {
            $responseData = $tenants;
        }

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $responseData,
        ]);
    }

    public function store(StoreTenantRequest $request)
    {
        $actor = $request->user();
        $data = $request->validated();
        $files = [
            'image' => $request->file('image'),
            'mykad_front' => $request->file('mykad.front_image'),
            'mykad_back' => $request->file('mykad.back_image'),
        ];

        try {
            $user = $this->service->store($data, Channel::Api, $actor, $files);
        } catch (PlanLimitExceededException $e) {
            return response()->json(['message' => $e->getMessage()], 406);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => __('Tenant store failed.'),
            ], 500);
        }

        return response()->json([
            'message' => __('Tenant save successfully.'),
            'data' => $user,
        ]);
    }

    public function show(string $id)
    {
        $tenant = User::with(['user_details'])->findOrFail($id);
        $this->authorize('view', $tenant);

        return response()->json([
            'message' => 'Data fetched successfully.',
            'data' => $tenant,
        ]);
    }

    public function update(UpdateTenantRequest $request, string $id)
    {
        $user = User::findOrFail($id);
        $actor = $request->user();
        $data = $request->validated();
        $files = [
            'image' => $request->file('image'),
            'mykad_front' => $request->file('mykad.front_image'),
            'mykad_back' => $request->file('mykad.back_image'),
        ];

        try {
            $user = $this->service->update($user, $data, Channel::Api, $actor, $files);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => __('Tenant updated failed.'),
            ], 500);
        }

        return response()->json([
            'message' => __('Tenant updated successfully.'),
            'data' => $user,
        ]);
    }
}
