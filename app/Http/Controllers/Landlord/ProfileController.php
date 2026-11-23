<?php

namespace App\Http\Controllers\Landlord;

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

    public function __construct(protected ProfileService $service) {}

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
            // D-FIX-validation: 'required|string:255px' era una regla rota
            // (el parámetro se ignoraba y un nombre largo moría en la DB 1406)
            'name' => 'required|string|max:255',
            // D-FIX-validation: clave 'email' duplicada — la segunda regla
            // sobrescribía a la primera; consolidada. D-IDOR: unique contra
            // el target real (usuario de sesión, no el id de la ruta).
            'email' => 'nullable|email|unique:users,email,'.auth()->id(),
            'phone' => 'nullable|array',
            'profile_type' => 'nullable|string:255',
            'image' => 'nullable|image|mimes:jpeg,png,gif|max:1024',
            'mykad.front_image' => 'nullable|image|mimes:jpeg,png,gif|max:1024',
            'mykad.back_image' => 'nullable|image|mimes:jpeg,png,gif|max:1024',
        ]);

        // D-IDOR (patrón Seg-29): el target es siempre el usuario de
        // sesión; el id de la ruta se ignora (antes resolvía cualquier
        // cuenta vía findOrFail — cuenta ajena sobrescrita, incluido
        // landlord_id e is_setupped).
        $user = User::findOrFail(Auth::id());

        // Comprobaciones y uploads antes de abrir la transacción (Fase 4 / 4.8)
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

        $oldPaths = [];

        if ($request->hasFile('image')) {
            if (! empty($user->image)) {
                $oldPaths[] = $user->image;
            }
            $image = $this->upload($request, 'image');
        } else {
            $image = $user->image ?? null;
        }

        if ($request->hasFile('mykad.front_image')) {
            if (! empty($user->user_details->mykad['front_image'])) {
                $oldPaths[] = $user->user_details->mykad['front_image'];
            }
            $frontImage = $this->upload($request, 'mykad.front_image');
        } else {
            $frontImage = $user->user_details->mykad['front_image'] ?? null;
        }

        if ($request->hasFile('mykad.back_image')) {
            if (! empty($user->user_details->mykad['back_image'])) {
                $oldPaths[] = $user->user_details->mykad['back_image'];
            }
            $backImage = $this->upload($request, 'mykad.back_image');
        } else {
            $backImage = $user->user_details->mykad['back_image'] ?? null;
        }

        try {
            $this->service->update($user, [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->input('phone'),
                'image' => $image,
                'profile_type' => $request->profile_type,
                'gender' => $request->gender,
                'birth_date' => $request->birth_date,
                'mykad_id' => $request->mykad_id,
                'address_info' => $request->input('address_info'),
                'mykad' => [
                    'front_image' => $frontImage ?? null,
                    'back_image' => $backImage ?? null,
                ],
                'password' => $request->password,
                'landlord_id' => auth()->id(),
            ], Channel::Landlord);

            // Borrado best-effort tras el commit
            foreach ($oldPaths as $path) {
                $this->deleteOldFile($path);
            }

            return response()->json([
                'message' => 'Profile update successfully',
                'redirect' => route('landlord.profile.index'),
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
