<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\HasUploader;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AcnooProfileController extends Controller
{
    use HasUploader;

    public function __construct()
    {
        $this->middleware('permission:profile-read')->only('index');
        $this->middleware('permission:profile-update')->only('update');
    }

    public function index()
    {
        $user = User::with('user_details')->where('id', Auth::user()->id)->first();
        $phone_codes = base_path('lang/phone_code.json');
        $phone_codes = json_decode(file_get_contents($phone_codes), true);
        return view('admin.profile.profile', compact('user', 'phone_codes'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'image' => 'nullable|image',
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

            $user->update($request->except('image', 'password', 'gender') + [
                'image' => $request->hasFile('image') ? $this->upload($request, 'image', $user->image) : $user->image,
            ] + ($request->filled('password') ? ['password' => Hash::make($request->password)] : []));

            $user->user_details()->updateOrCreate(
                ['user_id' => $user->id],
                ['gender' => $request->gender]
            );

            DB::commit();

            return response()->json([
                'message' => 'Profile update successfully',
                'redirect' => route('admin.profile.index')
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
