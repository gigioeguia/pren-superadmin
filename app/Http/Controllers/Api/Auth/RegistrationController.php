<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\User;
use App\Support\ErrorMessage;
use App\Support\Otp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class RegistrationController extends Controller
{
    public function __construct(protected Otp $otp) {}

    public function maanRegistration(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => Password::required(),
            'email' => 'required|email',
        ]);

        DB::beginTransaction();

        try {

            // Find free plan
            $free_plan = Plan::where('subscriptionPrice', '<=', 0)
                ->orWhere('offerPrice', '<=', 0)
                ->first();

            // Check existing user
            $user = User::where('email', $request->email)->first();

            if ($user && $user->is_setupped) {
                return response()->json([
                    'message' => 'This email is already exists.',
                ], 406);
            }

            // OTP settings
            $otpSettings = get_option('email-varification') ?? [];
            $otpStatus = $otpSettings['otp_status'] ?? 'off';

            $code = random_int(100000, 999999);

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

            // Create or update user. `role` no es fillable; se fuerza por propiedad
            // directa tras la creación (Seg-4). El evento `creating` del modelo
            // garantiza un valor por defecto 'landlord' si no se asigna.
            $user = User::updateOrCreate(
                ['email' => $request->email],
                $request->except('password', 'role') + [
                    'remember_token' => $otpStatus === 'on' ? $code : null,
                    'email_verified_at' => $otpStatus === 'on' ? $expire : now(),
                    'password' => Hash::make($request->password),
                ]
            );

            $user->role = 'landlord';
            $user->save();

            if ($otpStatus === 'off') {

                // assign free plan
                if ($free_plan) {

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
                    ]);

                    $user->update([
                        'plan_subscribe_id' => $subscribe->id,
                        'will_expire' => now()->addDays($duration),
                        'subscriptionDate' => now(),
                    ]);
                }

                DB::commit();

                Cache::forget('plan-data-'.$user->id);

                Auth::login($user);

                $token = $user->createToken('api-token')->plainTextToken;

                return response()->json([
                    'message' => 'Account created successfully. You are now logged in.',
                    'user' => $user,
                    'token' => $token,
                ]);
            }

            if (config('mail.queue')) {
                Mail::to($request->email)->queue(new WelcomeMail($data));
            } else {
                Mail::to($request->email)->send(new WelcomeMail($data));
            }

            // assign free plan
            if ($free_plan) {

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
                admin_msg: __('New landlord account has been created'),
                landlord_msg: __('You account has been successfully created.'),
                tenant_msg: __('You account has been successfully created.')
            );

            return response()->json([
                'message' => 'An otp code has been sent to your email. Please check and confirm.',
                'email' => $request->email,
                'otp_required' => true,
            ]);

        } catch (\Throwable $th) {
            report($th);

            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong. Please contact the admin.',
                'error' => __(ErrorMessage::GENERIC),
            ], 403);
        }
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

                $user->update([
                    'remember_token' => null,
                    'email_verified_at' => now(),
                ]);

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
