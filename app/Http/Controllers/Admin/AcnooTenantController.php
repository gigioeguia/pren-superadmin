<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Channel;
use App\Exceptions\PlanLimitExceededException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantRequest;
use App\Http\Requests\Tenant\UpdateTenantRequest;
use App\Models\Rent;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AcnooTenantController extends Controller
{
    public function __construct(protected TenantService $service)
    {
        $this->middleware('permission:tenants-read')->only('index', 'acnooFilter', 'file_download', 'exportExcel', 'exportCsv');
        $this->middleware('permission:tenants-create')->only('create', 'store');
        $this->middleware('permission:tenants-update')->only('edit', 'update');
        $this->middleware('permission:tenants-delete')->only('destroy', 'deleteAll');
    }

    public function index(Request $request)
    {
        $tenants = $this->service->listForChannel(Channel::Admin, auth()->user(), $request->all());

        return view('admin.tenants.index', compact('tenants'));
    }

    public function create()
    {
        $countries = base_path('lang/countrylist.json');
        $countries = json_decode(file_get_contents($countries), true);
        $phone_codes = base_path('lang/phone_code.json');
        $phone_codes = json_decode(file_get_contents($phone_codes), true);

        return view('admin.tenants.create', compact('countries', 'phone_codes'));
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
            $user = $this->service->store($data, Channel::Admin, $actor, $files);
        } catch (PlanLimitExceededException $e) {
            return response()->json(['message' => $e->getMessage()], 406);
        }

        return response()->json([
            'message' => 'Tenant save successfully',
            'redirect' => route('admin.tenants.index'),
        ]);
    }

    public function show(string $id)
    {
        $tenant = User::with([
            'user_details',
            'rents' => function ($query) {
                $query->where('status', 'active')
                    ->select('id', 'property_id', 'tenant_id', 'start_date', 'end_date', 'tenant_agreement');
            },
            'rents.security_deposit:id,rent_id,deposit_amount,rent_advance,utility_advance',
            'rents.property:id,category_id,puid,address_info',
            'rents.property.category:id,name',
            'rents.property.propertyDetail:property_id,property_info',
            'rents.rent_payments' => function ($query) {
                $query->select('rent_id', 'payment_status')
                    ->selectRaw('SUM(CASE WHEN payment_status = "paid" THEN subtotal_amount ELSE 0 END) as total_paid_rent')
                    ->selectRaw('SUM(CASE WHEN payment_status = "paid" AND MONTH(payment_date) = MONTH(CURRENT_DATE()) AND YEAR(payment_date) = YEAR(CURRENT_DATE()) THEN subtotal_amount ELSE 0 END) as this_month_payment')
                    ->selectRaw('SUM(CASE WHEN payment_status = "unpaid" THEN subtotal_amount ELSE 0 END) as due_rent')
                    ->groupBy('rent_id', 'payment_status');
            },
        ])->findOrFail($id);

        return view('admin.tenants.view', compact('tenant'));
    }

    public function edit(string $id)
    {
        $tenant = User::with('user_details')->findOrFail($id);
        $countries = base_path('lang/countrylist.json');
        $countries = json_decode(file_get_contents($countries), true);
        $phone_codes = base_path('lang/phone_code.json');
        $phone_codes = json_decode(file_get_contents($phone_codes), true);

        return view('admin.tenants.edit', compact('countries', 'tenant', 'phone_codes'));
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
            $this->service->update($user, $data, Channel::Admin, $actor, $files);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => __('Tenant updated failed.'),
            ], 500);
        }

        return response()->json([
            'message' => 'Tenant update successfully',
            'redirect' => route('admin.tenants.index'),
        ]);
    }

    public function destroy(string $id)
    {
        $tenant = User::findOrFail($id);

        try {
            $this->service->delete($tenant, Channel::Admin);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => __('Tenant deleted failed.')], 500);
        }

        return response()->json([
            'message' => 'Tenant deleted successfully',
            'redirect' => route('admin.tenants.index'),
        ]);
    }

    public function deleteAll(Request $request)
    {
        $ids = $request->ids ?? [];
        foreach ($ids as $id) {
            $user = User::find($id);
            if ($user) {
                $this->service->delete($user, Channel::Admin);
            }
        }

        return response()->json([
            'message' => 'Selected tenant deleted successfully',
            'redirect' => route('admin.tenants.index'),
        ]);
    }

    public function file_download(string $id)
    {
        $rent = Rent::findOrFail($id);

        if (is_null($rent->tenant_agreement)) {
            return response()->json(['message' => 'File not found']);
        }

        $filePath = $rent->tenant_agreement;

        if (Storage::exists($filePath)) {
            return Storage::download($filePath);
        }

        return response()->json(['message' => 'File not found']);
    }
}
