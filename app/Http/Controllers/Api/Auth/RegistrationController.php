<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\CreateAccountRequest;
use App\Mail\WelcomeMail;
use App\Models\User;
use App\Services\LandlordService;
use App\Support\ErrorMessage;
use App\Support\Otp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RegistrationController extends Controller
{
    public function __construct(protected Otp $otp, protected LandlordService $landlords) {}

    public function maanRegistration(CreateAccountRequest $request): JsonResponse
    {
        // Comprobaciones y cálculo de OTP antes de abrir la transacción (Fase 4 / 4.8)
        $free_plan = $this->landlords->freePlan();

        // Check existing user
        $existing_user = User::where('email', $request->email)->first();

        if ($existing_user && $existing_user->is_setupped) {
            return response()->json([
                'message' => 'This email is already exists.',
            ], 406);
        }

        // OTP settings
        $otpSettings = get_option('email-varification');
        $otpStatus = $otpSettings['otp_status'] ?? 'off';

        // OTP como string: remember_token/hash_equals operan sobre strings
        $code = (string) random_int(100000, 999999);

        $expire = null;
        $data = null;

        if ($otpStatus === 'on') {
            $expireSeconds = $this->otp->visibilityTimeInSeconds();

            $expire = now()->addSeconds($expireSeconds);

            $data = [
                'code' => $code,
                'name' => $request->name,
            ];
        }

        // tx externa + savepoints del service: una sola unidad atómica (User + trío), como el original
        DB::beginTransaction();

        try {
            $user = $this->landlords->createAccount(
                $request->validated(),
                Channel::Api,
                $otpStatus === 'on' ? $code : null,
                $expire
            );

            // assign free plan
            if ($free_plan) {
                $this->landlords->activateFreePlan($user, Channel::Api, $free_plan);
            }

            if ($otpStatus === 'off') {
                DB::commit();

                Auth::login($user);

                $token = $user->createToken('api-token')->plainTextToken;

                return response()->json([
                    'message' => 'Account created successfully. You are now logged in.',
                    'user' => $user,
                    'token' => $token,
                ]);
            }

            DB::commit();
        } catch (\Throwable $th) {
            report($th);

            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong. Please contact the admin.',
                'error' => __(ErrorMessage::GENERIC),
            ], 403);
        }

        // Envío de OTP tras el commit (Fase 4 / 4.8): sin E/S de red en la transacción.
        if (config('mail.queue')) {
            Mail::to($request->email)->queue(new WelcomeMail($data));
        } else {
            Mail::to($request->email)->send(new WelcomeMail($data));
        }

        sendNotification(
            $user->id,
            notify_users([$user->id]),
            admin_url: route('admin.landlords.index', ['id' => $user->id]),
            admin_msg: __('New landlord account has been created'),
            landlord_msg: __('You account has been successfully created.'),
            tenant_msg: __('You account has been successfully created.')
        );

        return response()->json([
            'message' => 'An otp code has been sent to your email. Please check and confirm.',
            'email' => $request->email,
            'otp_required' => true,
        ]);
    }

    public function submitOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|min:4|max:15',
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'status' => 404,
                'message' => __('User not found'),
            ], 404);
        }

        if ($user->remember_token !== null && hash_equals((string) $user->remember_token, (string) $request->otp)) {
            if ($user->email_verified_at > now()) {

                Auth::login($user);

                $user->forceFill([
                    'remember_token' => null,
                    'email_verified_at' => now(),
                ])->save();

                return response()->json([
                    'message' => 'Logged In successfully!',
                    'id' => $user->id,
                    'role' => $user->role,
                    'token' => $user->createToken('createToken')->plainTextToken,
                ]);
            } else {
                return response()->json([
                    'error' => __('The verification otp has been expired.'),
                ], 410);
            }
        } else {
            return response()->json([
                'error' => __('Invalid otp.'),
            ], 400);
        }
    }
}
