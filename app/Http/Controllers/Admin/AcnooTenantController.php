<?php

namespace App\Http\Controllers\Admin;

use App\Models\Rent;
use App\Models\User;
use App\Models\UserDetail;
use App\Helpers\HasUploader;
use Illuminate\Http\Request;
use App\Exports\AdminTenantExport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class AcnooTenantController extends Controller
{
    use HasUploader;

    public function __construct()
    {
        $this->middleware('permission:tenants-read')->only('index');
        $this->middleware('permission:tenants-create')->only('create','store');
        $this->middleware('permission:tenants-update')->only('edit','update');
        $this->middleware('permission:tenants-delete')->only('destroy', 'deleteAll');
    }

    public function index(Request $request)
    {
        $tenants = User::with(['user_details', 'rents',  'rents.property:id', 'rents.property.propertyDetail:property_id,property_info'])
            ->where('role', 'tenant')
            ->when($request->status, function ($query) use ($request) {
                if ($request->status == 'active') {
                    $query->whereHas('rents', function ($rentsQuery) {
                        $rentsQuery->where('status', 'active');
                    });
                } elseif ($request->status == 'expired') {
                    $query->whereHas('rents', function ($rentsQuery) {
                        $rentsQuery->where('status', 'expired');
                    })->whereDoesntHave('rents', function ($rentsQuery) {
                        $rentsQuery->where('status', 'active');
                    });
                }
            }, function ($query) {
                $query->whereDoesntHave('rents');
            })
            ->latest()
            ->paginate(10);

        return view('admin.tenants.index', compact('tenants'));
    }

    public function acnooFilter(Request $request)
    {
        $tenants = User::with(['user_details', 'rents'])->when($request->status, function ($query) use ($request) {
            if ($request->status == 'active') {
                $query->whereHas('rents', function ($rentsQuery) {
                    $rentsQuery->where('status', 'active');
                });
            } elseif ($request->status == 'expired') {
                $query->whereHas('rents', function ($rentsQuery) {
                    $rentsQuery->where('status', 'expired');
                })->whereDoesntHave('rents', function ($rentsQuery) {
                    $rentsQuery->where('status', 'active');
                });
            }
        }, function ($query) {
            $query->whereDoesntHave('rents');
        })
            ->when(request('search'), function ($q) {
                $q->where(function ($q) {
                    $search = request('search');
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orwhere('email', 'like', '%' . $search . '%')
                        ->orwhere('unique_id', 'like', '%' . $search . '%')
                        ->orWhere('profile_type', 'like', '%' . $search . '%')
                        ->orWhereHas('user_details', function ($query) use ($search) {
                            $query->where('gender', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 20);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.tenants.datas', compact('tenants'))->render()
            ]);
        }

        return redirect(url()->previous());
    }

    public function create()
    {
        $countries = base_path('lang/countrylist.json');
        $countries = json_decode(file_get_contents($countries), true);
        $phone_codes = base_path('lang/phone_code.json');
        $phone_codes = json_decode(file_get_contents($phone_codes), true);
        return view('admin.tenants.create', compact('countries', 'phone_codes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'phone' => 'nullable|array',
            'mobile_no' => 'nullable|string',
            'mobile_code' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,gif,svg|max:10240',
            'mykad.front_image' => 'nullable|image|mimes:jpeg,png,gif,svg|max:10240',
            'mykad.back_image' => 'nullable|image|mimes:jpeg,png,gif,svg|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $user =  User::create([
                'landlord_id' => auth()->user()->id,
                'image' => $request->hasFile('image') ? $this->upload($request, 'image') : null,
                'is_setupped' => 1,
                'name' => $request->name,
                'email' => $request->email,
                'role' => 'tenant',
                'phone' => $request->input('phone'),
                'password' => Hash::make($request->password),
                'profile_type' => $request->profile_type
            ]);

            $frontImage = $request->hasFile('mykad.front_image') ? $this->upload($request, 'mykad.front_image') :  null;
            $backImage = $request->hasFile('mykad.back_image') ? $this->upload($request, 'mykad.back_image') : null;

            UserDetail::create([
                'user_id' => $user->id,
                'gender' => $request->gender,
                'birth_date' => $request->birth_date,
                'mykad_id' => $request->mykad_id,
                'company_info' => $request->input('company_info'),
                'address_info' => $request->input('address_info'),
                'nominee_info' => $request->input('nominee_info'),
                'emergency_contact' => $request->input('emergency_contact'),
                'workplace' => $request->input('workplace'),
                'vehicles_info' => $request->input('vehicles_info'),
                'mykad' => [
                    'front_image' => $frontImage,
                    'back_image' => $backImage,
                ],
            ]);

            DB::commit();

            sendNotification($user->id, notify_users([$user->id, $user->landlord->id]), admin_url: route('admin.tenants.index', ['id' => $user->id]), landlord_url: route('landlord.tenants.index', ['id' => $user->id]),  admin_msg: __('New tenant has been added'), tenant_msg: __('You account has been successfully created.'), landlord_msg: __('New tenant has been added.'));

            return response()->json([
                'message' => 'Tenant save successfully',
                'redirect' => route('admin.tenants.index')

            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => __('Tenant store failed.'),
                'error' => $e->getMessage(),
            ], 500);
        }
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
        ])
            ->findOrFail($id);

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

    public function update(Request $request, string $id)
    {
        $request->validate([
            'phone' => 'nullable|array',
            'mobile_no' => 'nullable|string',
            'mobile_code' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
            'mykad.front_image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
            'mykad.back_image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
        ]);

        $user = User::findOrFail($id);

        DB::beginTransaction();

        try {

            if ($request->hasFile('image')) {
                if (!empty($user->image)) {
                    $oldImagePath = dirname($user->image);
                    Storage::deleteDirectory($oldImagePath);
                }
                $image = $this->upload($request, 'image');
            } else {
                $image = $user->image ?? null;
            }

            $user->update([
                'landlord_id' => auth()->user()->id,
                'image' => $image,
                'is_setupped' => 1,
                'name' => $request->name,
                'email' => $request->email,
                'role' => 'tenant',
                'phone' => $request->input('phone'),
                'password' => $request->password ? Hash::make($request->password) : $user->password,
                'profile_type' => $request->profile_type
            ]);

            if ($request->hasFile('mykad.front_image')) {
                if (!empty($user->user_details->mykad['front_image'])) {
                    $oldFrontImagePath = dirname($user->user_details->mykad['front_image']);
                    Storage::deleteDirectory($oldFrontImagePath);
                }
                $frontImage = $this->upload($request, 'mykad.front_image');
            } else {
                $frontImage = $user->user_details->mykad['front_image'] ?? null;
            }

            if ($request->hasFile('mykad.back_image')) {
                if (!empty($user->user_details->mykad['back_image'])) {
                    $oldBackImagePath = dirname($user->user_details->mykad['back_image']);
                    Storage::deleteDirectory($oldBackImagePath);
                }
                $backImage = $this->upload($request, 'mykad.back_image');
            } else {
                $backImage = $user->user_details->mykad['back_image'] ?? null;
            }

            $user->user_details->update([
                'user_id' => $user->id,
                'gender' => $request->gender,
                'birth_date' => $request->birth_date,
                'mykad_id' => $request->mykad_id,
                'company_info' => $request->input('company_info'),
                'address_info' => $request->input('address_info'),
                'nominee_info' => $request->input('nominee_info'),
                'emergency_contact' => $request->input('emergency_contact'),
                'workplace' => $request->input('workplace'),
                'vehicles_info' => $request->input('vehicles_info'),
                'mykad' => [
                    'front_image' => $frontImage,
                    'back_image' => $backImage,
                ],
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Tenant update successfully',
                'redirect' => route('admin.tenants.index')
            ]);
        } catch (\Exception $e) {
            return $e;
            DB::rollBack();
            return response()->json([
                'message' => __('Tenant updated failed.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $tenant = User::findOrFail($id);

            if (file_exists($tenant->image)) {
                Storage::delete($tenant->image);
            }

            $userDetail = $tenant->user_details;

            if ($userDetail) {
                if (file_exists($userDetail->mykad['front_image'])) {
                    Storage::delete($userDetail->mykad['front_image']);
                }
                if (file_exists($userDetail->mykad['back_image'])) {
                    Storage::delete($userDetail->mykad['back_image']);
                }
            }

            $tenant->delete();

            DB::commit();

            return response()->json([
                'message'   => ('Tenant deleted successfully'),
                'redirect'  => route('admin.tenants.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteAll(Request $request)
    {
        DB::beginTransaction();

        try {
            $tenants = User::whereIn('id', $request->ids)->get();

            foreach ($tenants as $tenant) {
                if (file_exists($tenant->image)) {
                    Storage::delete($tenant->image);
                }

                $userDetail = $tenant->user_details;

                if ($userDetail) {
                    if (file_exists($userDetail->mykad['front_image'])) {
                        Storage::delete($userDetail->mykad['front_image']);
                    }
                    if (file_exists($userDetail->mykad['back_image'])) {
                        Storage::delete($userDetail->mykad['back_image']);
                    }

                    $userDetail->delete();
                }

                $tenant->delete();
            }

            DB::commit();

            return response()->json([
                'message'   => ('Selected tenant deleted successfully'),
                'redirect'  => route('admin.tenants.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function file_download(string $id)
    {

        $rent = Rent::findOrFail($id);

        if (is_null($rent->tenant_agreement)) {
            return response()->json([
                'message' => 'File not found',
            ]);
        }

        $filePath = $rent->tenant_agreement;

        if (Storage::exists($filePath)) {
            return Storage::download($filePath);
        }
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new AdminTenantExport($request->status), 'tenant.xlsx');
    }

    public function exportCsv(Request $request)
    {
        return Excel::download(new AdminTenantExport($request->status), 'tenants.csv');
    }
}
