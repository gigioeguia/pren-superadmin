<?php

namespace App\Http\Controllers\Api;

use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Currency;
use App\Models\Facility;
use App\Models\Gateway;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use HasUploader;

    public function userInfo(Request $request)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $data = User::with(['user_details', 'plan_subscribe:id,plan_id', 'plan_subscribe.plan:id,subscriptionName'])->findOrFail($user->id);

            $currency = Currency::whereIsDefault(1)->first();
            $data->currency = $currency;

            return response()->json([
                'message' => __('Data fetched successfully.'),
                'data' => $data,
            ]);
        }
    }

    public function profileSetUp(Request $request)
    {
        $request->validate([
            'phone' => 'nullable|array',
            'mobile_no' => 'nullable|string',
            'mobile_code' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email,'.auth()->user()->id,
            'image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
            'mykad.front_image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
            'mykad.back_image' => 'nullable|image|mimes:jpeg,png,gif|max:10240',
        ]);

        $user = User::findOrFail(auth()->id());

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

            if ($user->user_details) {
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
            } else {
                $user->user_details()->create([
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
            }

            DB::commit();

            return response()->json([
                'message' => __('Profile updated successfully.'),
                'data' => $user->load('user_details', 'plan_subscribe:id,plan_id', 'plan_subscribe.plan:id,subscriptionName'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => __('Profile update failed.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function amenities()
    {
        $data['amenities'] = Amenity::where('status', 1)->get();
        count($data['amenities']) > 0
            ?
            $response = $this->respondWithSuccess($this->message[0], $data)
            :
            $response = $this->respondWithErrorNotFound($this->message[6], $data);

        return $response;
    }

    public function facilities()
    {
        $data['facilities'] = Facility::where('status', 1)->get();
        count($data['facilities']) > 0
            ?
            $response = $this->respondWithSuccess($this->message[0], $data)
            :
            $response = $this->respondWithErrorNotFound($this->message[6], $data);

        return $response;
    }

    public function offline_gateway()
    {
        $data = Gateway::select('id', 'is_manual', 'name', 'image', 'manual_data')->where('is_manual', 1)->get();

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $data,
        ]);
    }
}
