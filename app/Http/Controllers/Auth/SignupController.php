<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\User;
use App\Support\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password;

class SignupController extends Controller
{
    public function __construct(protected Otp $otp) {}

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => Password::required(),
            'email' => 'required|email',
        ]);

        if (! config('mail.username')) {
            return response()->json([
                'message' => __('Mail service is not configured. Please contact your administrator.'),
            ], 406);
        }

        // Comprobaciones y cálculo de OTP antes de abrir la transacción (Fase 4 / 4.8)
        $otpSettings = get_option('email-varification');
        $otpStatus = $otpSettings['otp_status'] ?? 'off';

        $user = User::where('email', $request->email)->first();

        if ($user && $user->is_setupped) {
            return response()->json([
                'message' => 'This email is already exists.',
            ], 406);
        }

        $code = random_int(100000, 999999);

        $data = [
            'code' => $code,
            'name' => $request->name,
        ];

        $expireSeconds = $this->otp->visibilityTimeInSeconds();
        $expire = now()->addSeconds($expireSeconds);

        DB::beginTransaction();

        try {
            if ($otpStatus === 'off') {

                $user = User::updateOrCreate(['email' => $request->email], $request->except('password', 'role') + [
                    'remember_token' => null,
                    'email_verified_at' => now(),
                    'password' => Hash::make($request->password),
                    'is_setupped' => 1,
                ]
                );

                $user->role = 'landlord';
                $user->save();

                session()->put('user_id', $user->id);
                session()->put('plan_id', $request->plan_id);

                DB::commit();

                Auth::login($user);

                return response()->json([
                    'message' => 'Account created successfully.',
                    'redirect' => URL::temporarySignedRoute('payments-gateways.index', now()->addHours(2), [
                        'plan_id' => $request->plan_id,
                        'landlord_id' => $user->id,
                    ]),
                ]);
            }

            $user = User::updateOrCreate(['email' => $request->email], $request->except('password', 'role') + [
                'remember_token' => $code,
                'role' => 'landlord',
                'email_verified_at' => $expire,
                'password' => Hash::make($request->password),
                'is_setupped' => 1,
            ]
            );

            $user->role = 'landlord';
            $user->save();

            session()->put('user_id', $user->id);
            session()->put('plan_id', $request->plan_id);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong. Please contact the admin.',
            ], 403);
        }

        // Envío de OTP tras el commit (Fase 4 / 4.8): sin E/S de red en la transacción.
        if (config('mail.queue')) {
            Mail::to($request->email)->queue(new WelcomeMail($data));
        } else {
            Mail::to($request->email)->send(new WelcomeMail($data));
        }

        return response()->json([
            'message' => 'An OTP code has been sent to your email. Please check and confirm.',
            'openModal' => true,
            'email' => $request->email,
            'otp_expiration' => $expireSeconds,
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
                'error' => __('User not found'),
            ], 404);
        }

        if ($user->remember_token !== null && hash_equals((string) $user->remember_token, (string) $request->otp)) {
            if ($user->email_verified_at > now()) {

                Auth::login($user);

                $user->update([
                    'remember_token' => null,
                    'email_verified_at' => now(),
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

        $plan = Plan::find(session('plan_id'));

        if ($plan) {
            $plan_price = ($plan->offerPrice ?? 0) > 0 ? $plan->offerPrice : $plan->subscriptionPrice;

            if ($plan_price <= 0) {
                // Calculate duration
                $duration = $plan->durationInDays();

                // Create free subscription directly
                $subscribe = PlanSubscribe::create([
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->subscriptionName,
                    'user_id' => $user->id,
                    'duration' => $duration,
                    'payment_status' => 'paid',
                ]);

                $user->update([
                    'plan_subscribe_id' => $subscribe->id,
                    'will_expire' => now()->addDays($duration),
                    'subscriptionDate' => now(),
                ]);

                $message = __('OTP verified successfully. Free plan activated!');
                $redirect_url = route('landlord.dashboard');
            } else {
                $message = __('Your account is verified. Proceed to payment.');
                $redirect_url = URL::temporarySignedRoute('payments-gateways.index', now()->addHours(2), [
                    'plan_id' => $plan->id,
                    'landlord_id' => $user->id,
                ]);
            }
        } else {
            $message = __('OTP verified successfully.');
            $redirect_url = route('landlord.dashboard');
        }

        return response()->json([
            'message' => $message,
            'redirect' => $redirect_url,
        ]);
    }

    public function otpResend(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $code = random_int(100000, 999999);
        $expire = now()->addSeconds((int) (config('auth.otp_visibility_time') ?? 60));

        $data = [
            'code' => $code,
            'name' => $request->name,
        ];

        if (config('mail.username')) {
            if (config('mail.queue')) {
                Mail::to($request->email)->queue(new WelcomeMail($data));
            } else {
                Mail::to($request->email)->send(new WelcomeMail($data));
            }
        } else {
            return response()->json([
                'message' => __('Mail service is not configured. Please contact your administrator.'),
            ], 406);
        }

        User::where('email', $request->email)->first()->update(['remember_token' => $code, 'email_verified_at' => $expire]);

        return response()->json([
            'message' => 'An otp code has been sent to your email. Please check and confirm.',
            'otp_expiration' => now()->diffInSeconds($expire),
        ]);
    }
}
