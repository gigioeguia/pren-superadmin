<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantRequest;
use App\Http\Requests\Tenant\UpdateTenantRequest;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Http\Request;

class AcnooTenantController extends Controller
{
    public function __construct(protected TenantService $service) {}

    public function index(Request $request)
    {
        $tenants = $this->service->listForChannel(Channel::Landlord, auth()->user(), $request->all());

        return view('landlord.tenants.index', compact('tenants'));
    }

    public function create()
    {
        $countries = base_path('lang/countrylist.json');
        $countries = json_decode(file_get_contents($countries), true);
        $phone_codes = base_path('lang/phone_code.json');
        $phone_codes = json_decode(file_get_contents($phone_codes), true);

        return view('landlord.tenants.create', compact('countries', 'phone_codes'));
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
            $this->service->store($data, Channel::Landlord, $actor, $files);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => __('Tenant store failed.'),
            ], 500);
        }

        return response()->json([
            'message' => 'Tenant save successfully',
            'redirect' => route('landlord.tenants.index'),
        ]);
    }

    public function show(string $id)
    {
        $tenant = User::with(['user_details', 'rents' => function ($query) {
            $query->where('status', 'active')
                ->select('id', 'property_id', 'tenant_id', 'start_date', 'end_date', 'tenant_agreement');
        }, 'rents.property:id,category_id,puid,address_info', 'rents.property.category:id,name', 'rents.property.propertyDetail:property_id,property_info'])->findOrFail($id);
        $this->authorize('view', $tenant);

        return view('landlord.tenants.view', compact('tenant'));
    }

    public function edit(string $id)
    {
        $tenant = User::with('user_details')->findOrFail($id);
        $countries = base_path('lang/countrylist.json');
        $countries = json_decode(file_get_contents($countries), true);
        $phone_codes = base_path('lang/phone_code.json');
        $phone_codes = json_decode(file_get_contents($phone_codes), true);

        return view('landlord.tenants.edit', compact('countries', 'tenant', 'phone_codes'));
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
            $this->service->update($user, $data, Channel::Landlord, $actor, $files);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => __('Tenant updated failed.'),
            ], 500);
        }

        return response()->json([
            'message' => 'Tenant update successfully',
            'redirect' => route('landlord.tenants.index'),
        ]);
    }

    public function destroy(string $id)
    {
        $tenant = User::findOrFail($id);

        try {
            $this->service->delete($tenant, Channel::Landlord);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => __('Tenant deleted failed.')], 500);
        }

        return response()->json([
            'message' => 'Tenant deleted successfully',
            'redirect' => route('landlord.tenants.index'),
        ]);
    }

    public function deleteAll(Request $request)
    {
        $ids = $request->ids ?? [];
        foreach ($ids as $id) {
            $user = User::find($id);
            if ($user) {
                $this->service->delete($user, Channel::Landlord);
            }
        }

        return response()->json([
            'message' => 'Selected tenant deleted successfully',
            'redirect' => route('landlord.tenants.index'),
        ]);
    }
}
