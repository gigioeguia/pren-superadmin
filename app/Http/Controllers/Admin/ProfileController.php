<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Channel;
use App\Helpers\HasUploader;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ProfileService;
use App\Support\ErrorMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    use HasUploader;

    public function __construct(protected ProfileService $service)
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

        // Seg-29: el target es siempre el usuario de sesión; el id de la
        // ruta se ignora (antes resolvía cualquier cuenta vía findOrFail).
        // Auth::id() en vez de $request->user(): tipado limpio para PHPStan
        // (los guards de config declaran modelos fantasma).
        $user = User::findOrFail(Auth::id());

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

        try {
            $this->service->update($user, [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->input('phone'),
                'image' => $image,
                'gender' => $request->gender,
                'password' => $request->filled('password') ? $request->password : null,
            ], Channel::Admin);

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

            return response()->json([
                'message' => __('Profile updated failed.'),
                'error' => __(ErrorMessage::GENERIC),
            ], 500);
        }
    }
}
