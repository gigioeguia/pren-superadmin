<?php

namespace App\Http\Controllers\Api;

use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AcnooTenantController extends Controller
{
    use HasUploader;

    public function index(Request $request)
    {
        $landlordId = auth()->id();

        $query = User::where('landlord_id', $landlordId)
            ->when($request->status, function ($query) use ($request) {
                $query->whereHas('rents', function ($rentsQuery) use ($request) {
                    $rentsQuery->where('status', $request->status);
                });
            })
            ->when($request->search, function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('id', $search);
                });
            })
            ->latest();
        if ($request->has('no_paginate') && $request->no_paginate == true) {
            $tenants = User::select('id', 'unique_id', 'landlord_id', 'name')->where('landlord_id', $landlordId)->get();
            $responseData = [
                'data' => $tenants,
            ];
        } else {
            $tenants = $query->paginate($request->per_page ?? 10);
            $responseData = $tenants;
        }

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $responseData,
        ]);
    }

    public function store(Request $request)
    {
        $plan = plan_data()->plan;
        $tenant_count = User::where([['landlord_id', auth()->id()], ['role', 'tenant']])->count();

        if ($plan->number_of_tenant !== -1 && $tenant_count >= $plan->number_of_tenant) {
            return response()->json(['message' => __('Your Tenant addition limit has been reached. Your current limit is '.$plan->number_of_tenant)], 406);
        }

        $request->validate([
            'phone' => 'nullable|array',
            'mobile_no' => 'nullable|string',
            'mobile_code' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email',
            'image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
            'mykad.front_image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
            'mykad.back_image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'landlord_id' => auth()->user()->id,
                'image' => $request->hasFile('image') ? $this->upload($request, 'image') : null,
                'is_setupped' => 0,
                'name' => $request->name,
                'email' => $request->email,
                'role' => 'tenant',
                'phone' => $request->input('phone'),
                'password' => Hash::make($request->password),
                'profile_type' => $request->profile_type,
            ]);

            $frontImage = $request->hasFile('mykad.front_image') ? $this->upload($request, 'mykad.front_image') : null;
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

            $code = random_int(100000, 999999);
            $expire = now()->addMinutes(env('OTP_VISIBILITY_TIME') ?? 3);
            $data = [
                'code' => $code,
                'name' => $request->name,
            ];

            $user->update([
                'remember_token' => $code,
                'email_verified_at' => $expire,
            ]);

            if (env('QUEUE_MAIL')) {
                Mail::to($request->email)->queue(new WelcomeMail($data));
            } else {
                Mail::to($request->email)->send(new WelcomeMail($data));
            }

            DB::commit();

            sendNotification($user->id, notify_users([$user->landlord_id, $user->id]), admin_url: route('admin.tenants.index', ['id' => $user->id]), landlord_url: route('landlord.tenants.index', ['id' => $user->id]), admin_msg: __('New tenant has been added'), landlord_msg: __('You have successfully created a new tenant.'), tenant_msg: __('Your account has been created'));

            return response()->json([
                'message' => __('Tenant save successfully.'),
                'data' => $user->load('user_details'),
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
        $data = User::with([
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
                $query->select('rent_id', 'payment_status', 'late_fee')
                    ->selectRaw('SUM(CASE WHEN payment_status = "paid" THEN subtotal_amount ELSE 0 END) as total_paid_rent')
                    ->selectRaw('SUM(CASE WHEN payment_status = "paid" AND MONTH(payment_date) = MONTH(CURRENT_DATE()) AND YEAR(payment_date) = YEAR(CURRENT_DATE()) THEN subtotal_amount ELSE 0 END) as this_month_payment')
                    ->selectRaw('SUM(CASE WHEN payment_status = "unpaid" THEN subtotal_amount ELSE 0 END) as due_rent')
                    ->groupBy('rent_id', 'payment_status', 'late_fee');
            },
        ])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $data,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'phone' => 'nullable|array',
            'mobile_no' => 'nullable|string',
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
                'landlord_id' => auth()->user()->id,
                'image' => $image,
                'is_setupped' => 1,
                'name' => $request->name,
                'email' => $request->email,
                'role' => 'tenant',
                'phone' => $request->input('phone'),
                // 'password' => Hash::make($request->password),
                'profile_type' => $request->profile_type,
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
                'message' => __('Tenant updated successfully.'),
                'data' => $user->load('user_details'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => __('Tenant updated failed.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
