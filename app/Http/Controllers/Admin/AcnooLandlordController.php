<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminLandlordExport;
use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class AcnooLandlordController extends Controller
{
    use HasUploader;

    public function __construct()
    {
        $this->middleware('permission:landlords-read')->only('index');
        $this->middleware('permission:landlords-create')->only('create', 'store');
        $this->middleware('permission:landlords-update')->only('edit', 'update');
        $this->middleware('permission:landlords-delete')->only('destroy', 'deleteAll');
    }

    public function index(Request $request)
    {
        $landlords = User::with(['user_details', 'plan_subscribe:id,plan_name,number_of_property,number_of_tenant,number_of_labor'])
            ->where('role', 'landlord')
            ->when($request->id, function ($query) use ($request) {
                $query->where('id', $request->id);
            })
            ->when($request->status == 'active', function ($query) {
                $query->whereDate('will_expire', '>=', today());
            })
            ->when($request->status == 'expired', function ($query) {

                $query->whereDate('will_expire', '<=', today());
            })
            ->latest()
            ->paginate(10);

        return view('admin.landlords.index', compact('landlords'));
    }

    public function acnooFilter(Request $request)
    {
        $landlords = User::with('user_details')
            ->where('role', 'landlord')
            ->when(request('search'), function ($q) {
                $q->where(function ($q) {
                    $search = request('search');
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orwhere('email', 'like', '%'.$search.'%')
                        ->orWhereHas('user_details', function ($query) use ($search) {
                            $query->where('gender', 'like', '%'.$search.'%');
                        });
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 20);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.landlords.datas', compact('landlords'))->render(),
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

        return view('admin.landlords.create', compact('countries', 'phone_codes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'phone' => 'nullable|array',
            'phone.mobile_no' => 'nullable|string|max:15',
            'mobile_code' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,gif,svg|max:10240',
            'mykad.front_image' => 'nullable|image|mimes:jpeg,png,gif,svg|max:10240',
            'mykad.back_image' => 'nullable|image|mimes:jpeg,png,gif,svg|max:10240',
        ], [
            'phone.mobile_no.max' => 'The phone must not be greater than 15 characters.',
        ]);

        // Find free plan
        $free_plan = Plan::where('subscriptionPrice', '<=', 0)
            ->orWhere('offerPrice', '<=', 0)
            ->first();

        DB::beginTransaction();
        try {

            $user = User::create([
                'image' => $request->image ? $this->upload($request, 'image') : null,
                'is_setupped' => 1,
                'name' => $request->name,
                'email' => $request->email,
                'plan_subscribe_id' => $request->plan_subscribe_id,
                'role' => 'landlord',
                'phone' => $request->input('phone'),
                'password' => Hash::make($request->password),
            ]);

            $frontImage = $request->hasFile('mykad.front_image') ? $this->upload($request, 'mykad.front_image') : null;
            $backImage = $request->hasFile('mykad.back_image') ? $this->upload($request, 'mykad.back_image') : null;

            UserDetail::create([
                'user_id' => $user->id,
                'gender' => $request->gender,
                'mykad_id' => $request->mykad_id,
                'address_info' => $request->input('address_info'),
                'mykad' => [
                    'front_image' => $frontImage,
                    'back_image' => $backImage,
                ],
            ]);

            // Assign free plan if available
            if ($free_plan) {
                // in days
                $duration = 0;
                if ($free_plan->duration_type == 'month') {
                    $duration = $free_plan->duration * 30;
                } elseif ($free_plan->duration_type == 'year') {
                    $duration = $free_plan->duration * 365;
                } elseif ($free_plan->duration_type == 'day') {
                    $duration = $free_plan->duration;
                }

                $subscribe = PlanSubscribe::create([
                    'plan_id' => $free_plan->id,
                    'user_id' => $user->id,
                    'duration' => $duration,
                    'plan_name' => $free_plan->subscriptionName,
                    'number_of_property' => $free_plan->number_of_property,
                    'number_of_tenant' => $free_plan->number_of_tenant,
                    'number_of_labor' => $free_plan->number_of_labor,
                    'payment_status' => 'paid',
                ]);

                $user->update([
                    'plan_subscribe_id' => $subscribe->id,
                    'will_expire' => now()->addDays($duration),
                    'subscriptionDate' => now(),
                ]);
            }

            DB::commit();

            Cache::forget('plan-data-'.$user->id);

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
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => __('Landlord created failed.'),
                'error' => $e->getMessage(),
            ], 500);
        }
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

    public function update(Request $request, string $id)
    {
        $request->validate([
            'phone' => 'nullable|array',
            'mobile_no' => 'nullable|string|max:15',
            'mobile_code' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email,'.$id,
            'image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
            'mykad.front_image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
            'mykad.back_image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
        ]);

        $user = User::findOrFail($id);

        DB::beginTransaction();

        try {

            if ($request->hasFile('image')) {
                if (! empty($user->image)) {
                    $oldImagePath = dirname($user->image);
                    Storage::deleteDirectory($oldImagePath);
                }
                $image = $this->upload($request, 'image');
            } else {
                $image = $user->image ?? null;
            }

            $user->update([
                'image' => $image,
                'is_setupped' => 1,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->input('phone'),
                'password' => $request->password ? Hash::make($request->password) : $user->password,
            ]);

            if ($request->hasFile('mykad.front_image')) {
                if (! empty($user->user_details->mykad['front_image'])) {
                    $oldFrontImagePath = dirname($user->user_details->mykad['front_image']);
                    Storage::deleteDirectory($oldFrontImagePath);
                }
                $frontImage = $this->upload($request, 'mykad.front_image');
            } else {
                $frontImage = $user->user_details->mykad['front_image'] ?? null;
            }

            if ($request->hasFile('mykad.back_image')) {
                if (! empty($user->user_details->mykad['back_image'])) {
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
                'mykad_id' => $request->mykad_id,
                'address_info' => $request->input('address_info'),
                'mykad' => [
                    'front_image' => $frontImage,
                    'back_image' => $backImage,
                ],
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Landlord update successfully',
                'redirect' => route('admin.landlords.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => __('Landlord updated failed.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {
            $landlord = User::findOrFail($id);

            if (file_exists($landlord->image)) {
                Storage::delete($landlord->image);
            }

            $userDetail = $landlord->user_details;

            if ($userDetail) {
                if (file_exists($userDetail->mykad['front_image'])) {
                    Storage::delete($userDetail->mykad['front_image']);
                }

                if (file_exists($userDetail->mykad['back_image'])) {
                    Storage::delete($userDetail->mykad['back_image']);
                }
            }

            $landlord->delete();

            DB::commit();

            return response()->json([
                'message' => __('Landlord deleted successfully'),
                'redirect' => route('admin.landlords.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong: '.$e->getMessage(),
            ], 500);
        }
    }

    public function deleteAll(Request $request)
    {
        DB::beginTransaction();

        try {
            $landlords = User::whereIn('id', $request->ids)->get();

            foreach ($landlords as $landlord) {
                if (file_exists($landlord->image)) {
                    Storage::delete($landlord->image);
                }

                $userDetail = $landlord->user_details;

                if ($userDetail) {
                    if (file_exists($userDetail->mykad['front_image'])) {
                        Storage::delete($userDetail->mykad['front_image']);
                    }
                    if (file_exists($userDetail->mykad['back_image'])) {
                        Storage::delete($userDetail->mykad['back_image']);
                    }

                    $userDetail->delete();
                }

                $landlord->delete();
            }

            DB::commit();

            return response()->json([
                'message' => ('Selected landlord deleted successfully'),
                'redirect' => route('admin.landlords.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong: '.$e->getMessage(),
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new AdminLandlordExport($request), 'landlords.xlsx');
    }

    public function exportCsv(Request $request)
    {
        return Excel::download(new AdminLandlordExport($request), 'landlords.csv');
    }
}
