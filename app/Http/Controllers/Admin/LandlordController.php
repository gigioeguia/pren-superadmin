<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminLandlordExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\StoreLandlordRequest;
use App\Http\Requests\Landlord\UpdateLandlordRequest;
use App\Models\User;
use App\Services\LandlordService;
use App\Support\ErrorMessage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

class LandlordController extends Controller
{
    public function __construct(protected LandlordService $landlords)
    {
        $this->middleware('permission:landlords-read')->only('index', 'filter', 'exportExcel', 'exportCsv');
        $this->middleware('permission:landlords-create')->only('create', 'store');
        $this->middleware('permission:landlords-update')->only('edit', 'update');
        $this->middleware('permission:landlords-delete')->only('destroy', 'deleteAll');
    }

    public function index(Request $request)
    {
        $landlords = $this->landlords->list($request->only(['id', 'status']));

        return view('admin.landlords.index', compact('landlords'));
    }

    public function create()
    {
        $countries = base_path('lang/countrylist.json');
        $countries = json_decode(file_get_contents($countries), true);
        $phone_codes = base_path('lang/phone_code.json');
        $phone_codes = json_decode(file_get_contents($phone_codes), true);

        return view('admin.landlords.create', compact('countries', 'phone_codes'));
    }

    public function store(StoreLandlordRequest $request)
    {
        try {
            $user = $this->landlords->store($request->validated(), $this->landlordFiles($request));
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'message' => __('Landlord created failed.'),
                'error' => __(ErrorMessage::GENERIC),
            ], 500);
        }

        sendNotification(
            $user->id,
            notify_users([$user->id]),
            admin_url: route('admin.landlords.index', ['id' => $user->id]),
            admin_msg: __('A new landlord account has been created successfully.'),
            landlord_msg: __('Your landlord account has been created.')
        );

        return response()->json([
            'message' => __('Landlord created successfully.'),
            'redirect' => route('admin.landlords.index'),
        ]);
    }

    public function edit(string $id)
    {
        $landlord = User::with('user_details')->findOrFail($id);
        $countries = base_path('lang/countrylist.json');
        $countries = json_decode(file_get_contents($countries), true);
        $phone_codes = base_path('lang/phone_code.json');
        $phone_codes = json_decode(file_get_contents($phone_codes), true);

        return view('admin.landlords.edit', compact('landlord', 'countries', 'phone_codes'));
    }

    public function update(UpdateLandlordRequest $request, string $id)
    {
        // findOrFail fuera del try: 404 para id inexistente (comportamiento actual)
        $user = User::findOrFail($id);

        try {
            $this->landlords->update($user, $request->validated(), $this->landlordFiles($request));
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'message' => __('Landlord updated failed.'),
                'error' => __(ErrorMessage::GENERIC),
            ], 500);
        }

        return response()->json([
            'message' => 'Landlord update successfully',
            'redirect' => route('admin.landlords.index'),
        ]);
    }

    public function destroy(string $id)
    {
        try {
            // findOrFail dentro del try: 500 genérico para id inexistente (comportamiento actual)
            $this->landlords->delete(User::findOrFail($id));
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'message' => __(ErrorMessage::GENERIC),
            ], 500);
        }

        return response()->json([
            'message' => __('Landlord deleted successfully'),
            'redirect' => route('admin.landlords.index'),
        ]);
    }

    public function deleteAll(Request $request)
    {
        try {
            $this->landlords->deleteMany($request->input('ids') ?? []);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'message' => __(ErrorMessage::GENERIC),
            ], 500);
        }

        return response()->json([
            'message' => 'Selected landlord deleted successfully',
            'redirect' => route('admin.landlords.index'),
        ]);
    }

    public function filter(Request $request)
    {
        $rows = $this->landlords->listForFilter(
            $request->only(['search']),
            (int) ($request->input('per_page') ?? 20)
        );

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.landlords.datas', ['landlords' => $rows])->render(),
            ]);
        }

        return redirect(url()->previous());
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new AdminLandlordExport($request), 'landlords.xlsx');
    }

    public function exportCsv(Request $request)
    {
        return Excel::download(new AdminLandlordExport($request), 'landlords.csv');
    }

    /**
     * @return array<string, UploadedFile|null>
     */
    private function landlordFiles(Request $request): array
    {
        return [
            'image' => $request->file('image'),
            'mykad_front' => $request->file('mykad.front_image'),
            'mykad_back' => $request->file('mykad.back_image'),
        ];
    }
}
