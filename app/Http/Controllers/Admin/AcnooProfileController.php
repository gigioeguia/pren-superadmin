<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ErrorMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        // Comprobaciones y upload antes de abrir la transacción (Fase 4 / 4.8)
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

        $oldImagePath = null;
        $image = $user->image;
        if ($request->hasFile('image')) {
            if ($user->image) {
                $oldImagePath = $user->image;
            }
            $image = $this->upload($request, 'image');
        }

        DB::beginTransaction();

        try {

            $user->update($request->except('image', 'password', 'gender') + [
                'image' => $image,
            ] + ($request->filled('password') ? ['password' => Hash::make($request->password)] : []));

            $user->user_details()->updateOrCreate(
                ['user_id' => $user->id],
                ['gender' => $request->gender]
            );

            DB::commit();

            // Borrado best-effort tras el commit
            if ($oldImagePath) {
                $this->deleteOldFile($oldImagePath);
            }

            return response()->json([
                'message' => 'Profile update successfully',
                'redirect' => route('admin.profile.index'),
            ]);
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();

            return response()->json([
                'message' => __('Profile updated failed.'),
                'error' => __(ErrorMessage::GENERIC),
            ], 500);
        }
    }
}
