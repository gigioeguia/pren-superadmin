<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminLandlordExport;
use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\User;
use App\Models\UserDetail;
use App\Support\ErrorMessage;
use App\Traits\HasAcnooActions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AcnooLandlordController extends Controller
{
    use HasAcnooActions;
    use HasUploader;

    public function __construct()
    {
        $this->middleware('permission:landlords-read')->only('index', 'acnooFilter', 'exportExcel', 'exportCsv');
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
            'image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
            'mykad.front_image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
            'mykad.back_image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
        ], [
            'phone.mobile_no.max' => 'The phone must not be greater than 15 characters.',
        ]);

        // Find free plan
        $free_plan = Plan::where('subscriptionPrice', '<=', 0)
            ->orWhere('offerPrice', '<=', 0)
            ->first();

        // Uploads antes de abrir la transacción (Fase 4 / 4.8)
        $image = $request->image ? $this->upload($request, 'image') : null;
        $frontImage = $request->hasFile('mykad.front_image') ? $this->upload($request, 'mykad.front_image') : null;
        $backImage = $request->hasFile('mykad.back_image') ? $this->upload($request, 'mykad.back_image') : null;

        DB::beginTransaction();
        try {

            $user = User::create([
                'image' => $image,
                'is_setupped' => 1,
                'name' => $request->name,
                'email' => $request->email,
                'plan_subscribe_id' => $request->plan_subscribe_id,
                'phone' => $request->input('phone'),
                'password' => Hash::make($request->password),
            ]);

            $user->role = 'landlord';
            $user->save();

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
                $duration = $free_plan->durationInDays();

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
            report($e);
            DB::rollBack();

            return response()->json([
                'message' => __('Landlord created failed.'),
                'error' => __(ErrorMessage::GENERIC),
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

        // Uploads antes de abrir la transacción; directorios viejos se borran tras el commit (Fase 4 / 4.8)
        $oldDirectories = [];

        if ($request->hasFile('image')) {
            if (! empty($user->image)) {
                $oldDirectories[] = dirname($user->image);
            }
            $image = $this->upload($request, 'image');
        } else {
            $image = $user->image ?? null;
        }

        if ($request->hasFile('mykad.front_image')) {
            if (! empty($user->user_details->mykad['front_image'])) {
                $oldDirectories[] = dirname($user->user_details->mykad['front_image']);
            }
            $frontImage = $this->upload($request, 'mykad.front_image');
        } else {
            $frontImage = $user->user_details->mykad['front_image'] ?? null;
        }

        if ($request->hasFile('mykad.back_image')) {
            if (! empty($user->user_details->mykad['back_image'])) {
                $oldDirectories[] = dirname($user->user_details->mykad['back_image']);
            }
            $backImage = $this->upload($request, 'mykad.back_image');
        } else {
            $backImage = $user->user_details->mykad['back_image'] ?? null;
        }

        DB::beginTransaction();

        try {

            $user->update([
                'image' => $image,
                'is_setupped' => 1,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->input('phone'),
                'password' => $request->password ? Hash::make($request->password) : $user->password,
            ]);

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

            // Borrado best-effort tras el commit
            foreach ($oldDirectories as $directory) {
                Storage::deleteDirectory($directory);
            }

            return response()->json([
                'message' => 'Landlord update successfully',
                'redirect' => route('admin.landlords.index'),
            ]);
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();

            return response()->json([
                'message' => __('Landlord updated failed.'),
                'error' => __(ErrorMessage::GENERIC),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {
            $landlord = User::findOrFail($id);

            // Rutas capturadas dentro de la transacción; borrado tras el commit (Fase 4 / 4.8)
            $oldPaths = [];
            if ($landlord->image) {
                $oldPaths[] = $landlord->image;
            }

            $userDetail = $landlord->user_details()->first();

            if ($userDetail) {
                $frontImage = $userDetail->getAttribute('mykad')['front_image'] ?? null;
                $backImage = $userDetail->getAttribute('mykad')['back_image'] ?? null;

                if ($frontImage) {
                    $oldPaths[] = $frontImage;
                }

                if ($backImage) {
                    $oldPaths[] = $backImage;
                }
            }

            $landlord->delete();

            DB::commit();

            // Borrado best-effort tras el commit
            foreach ($oldPaths as $path) {
                if (file_exists($path)) {
                    Storage::delete($path);
                }
            }

            return response()->json([
                'message' => __('Landlord deleted successfully'),
                'redirect' => route('admin.landlords.index'),
            ]);
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();

            return response()->json([
                'message' => __(ErrorMessage::GENERIC),
            ], 500);
        }
    }

    public function deleteAll(Request $request)
    {
        DB::beginTransaction();

        try {
            $landlords = User::whereIn('id', $request->ids)->get();

            // Rutas capturadas dentro de la transacción; borrado tras el commit (Fase 4 / 4.8)
            $oldPaths = [];

            foreach ($landlords as $landlord) {
                if ($landlord->image) {
                    $oldPaths[] = $landlord->image;
                }

                $userDetail = $landlord->user_details()->first();

                if ($userDetail) {
                    $frontImage = $userDetail->getAttribute('mykad')['front_image'] ?? null;
                    $backImage = $userDetail->getAttribute('mykad')['back_image'] ?? null;

                    if ($frontImage) {
                        $oldPaths[] = $frontImage;
                    }
                    if ($backImage) {
                        $oldPaths[] = $backImage;
                    }

                    $userDetail->delete();
                }

                $landlord->delete();
            }

            DB::commit();

            // Borrado best-effort tras el commit
            foreach ($oldPaths as $path) {
                if (file_exists($path)) {
                    Storage::delete($path);
                }
            }

            return response()->json([
                'message' => ('Selected landlord deleted successfully'),
                'redirect' => route('admin.landlords.index'),
            ]);
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();

            return response()->json([
                'message' => __(ErrorMessage::GENERIC),
            ], 500);
        }
    }

    // Config de HasAcnooActions (spec-trait-acnoo-fase0 §4): valores verbatim del controlador.

    protected function acnooModel(): string
    {
        return User::class;
    }

    protected function acnooQuery(Request $request): Builder
    {
        return User::with('user_details')
            ->where('role', 'landlord')
            ->when($request->input('search'), function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $search = $request->input('search');
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orwhere('email', 'like', '%'.$search.'%')
                        ->orWhereHas('user_details', function ($query) use ($search) {
                            $query->where('gender', 'like', '%'.$search.'%');
                        });
                });
            })
            ->latest();
    }

    protected function acnooView(): string
    {
        return 'admin.landlords.datas';
    }

    protected function acnooViewData(LengthAwarePaginator $rows): array
    {
        return ['landlords' => $rows];
    }

    protected function acnooPerPage(): int
    {
        return 20;
    }

    protected function acnooExport(Request $request): object
    {
        return new AdminLandlordExport($request);
    }

    protected function acnooExcelFilename(): string
    {
        return 'landlords';
    }

    protected function acnooCsvFilename(): string
    {
        return 'landlords';
    }
}
