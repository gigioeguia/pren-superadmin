<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Channel;
use App\Exceptions\PlanLimitExceededException;
use App\Exports\AdminTenantExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantRequest;
use App\Http\Requests\Tenant\UpdateTenantRequest;
use App\Models\Rent;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class TenantController extends Controller
{
    public function __construct(protected TenantService $service)
    {
        $this->middleware('permission:tenants-read')->only('index', 'filter', 'file_download', 'exportExcel', 'exportCsv');
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
        return view('admin.tenants.create', $this->formSelects());
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
        $tenant = $this->service->loadForShow((int) $id);

        return view('admin.tenants.view', compact('tenant'));
    }

    public function edit(string $id)
    {
        $tenant = User::with('user_details')->findOrFail($id);

        return view('admin.tenants.edit', array_merge($this->formSelects(), compact('tenant')));
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

    /**
     * Rutas restauradas de 4.2 (spec residuales 5.13): el JS de la vista
     * espera el shape del trait (JSON {data} para XHR, redirect si no).
     */
    public function filter(Request $request)
    {
        $tenants = $this->service->listForChannel(Channel::Admin, auth()->user(), array_merge($request->all(), ['per_page' => 20]));

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.tenants.datas', ['tenants' => $tenants])->render(),
            ]);
        }

        return redirect(url()->previous());
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new AdminTenantExport($request->status), 'tenant.xlsx');
    }

    public function exportCsv(Request $request)
    {
        return Excel::download(new AdminTenantExport($request->status), 'tenants.csv');
    }

    /**
     * Selects estáticos de los formularios create/edit (lang/*.json).
     *
     * @return array<string, mixed>
     */
    private function formSelects(): array
    {
        return [
            'countries' => json_decode((string) file_get_contents(base_path('lang/countrylist.json')), true),
            'phone_codes' => json_decode((string) file_get_contents(base_path('lang/phone_code.json')), true),
        ];
    }
}
