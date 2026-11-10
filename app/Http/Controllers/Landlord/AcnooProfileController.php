<?php

namespace App\Http\Controllers\Landlord;

use App\Models\User;
use App\Helpers\HasUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AcnooProfileController extends Controller
{
    use HasUploader;

    public function index()
    {
        $user = User::with('user_details')->where('id', Auth::user()->id)->first();
        $countries = base_path('lang/countrylist.json');
        $countries = json_decode(file_get_contents($countries), true);
        $phone_codes = base_path('lang/phone_code.json');
        $phone_codes = json_decode(file_get_contents($phone_codes), true);

        return view('landlord.profile.index', compact('user', 'countries', 'phone_codes'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string:255px',
            'email' => 'required|email',
            'phone' => 'nullable|array',
            'profile_type' => 'nullable|string:255',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'image' => 'nullable|image|mimes:jpeg,png,gif,svg|max:1024',
            'mykad.front_image' => 'nullable|image|mimes:jpeg,png,gif,svg|max:1024',
            'mykad.back_image' => 'nullable|image|mimes:jpeg,png,gif,svg|max:1024',
        ]);

        $user = User::findOrFail($id);

        DB::beginTransaction();

        try {

            if ($request->password || $request->current_password) {
                if (Hash::check($request->current_password, $user->password)) {
                    $request->validate([
                        'current_password' => 'required|string',
                        'password' => 'nullable|string|confirmed',
                    ]);
                } else {
                    return response()->json(__('Current Password does not match with old password'), 404);
                }
            }

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
                'phone' => $request->input('phone'),
                'profile_type' => $request->profile_type,
                'password' => $request->password ? Hash::make($request->password) : $user->password
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

            // Before updating user details, ensure it exists
            $userDetails = $user->user_details;

            if (!$userDetails) {
                $userDetails = $user->user_details()->create([
                    'user_id' => $user->id,
                    'gender' => $request->gender,
                    'birth_date' => $request->birth_date,
                    'mykad_id' => $request->mykad_id,
                    'address_info' => $request->input('address_info'),
                    'mykad' => [
                        'front_image' => $frontImage ?? null,
                        'back_image' => $backImage ?? null,
                    ],
                ]);
            } else {
                $userDetails->update([
                    'gender' => $request->gender,
                    'birth_date' => $request->birth_date,
                    'mykad_id' => $request->mykad_id,
                    'address_info' => $request->input('address_info'),
                    'mykad' => [
                        'front_image' => $frontImage ?? null,
                        'back_image' => $backImage ?? null,
                    ],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Profile update successfully',
                'redirect' => route('landlord.profile.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => __('Profile updated failed.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
