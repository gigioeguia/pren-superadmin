<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Plan;
use App\Models\PlanSubscribe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class SignupController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|min:6|max:100',
            'email' => 'required|email',
        ]);

        if (! env('MAIL_USERNAME')) {
            return response()->json([
                'message' => __('Mail service is not configured. Please contact your administrator.'),
            ], 406);
        }

        DB::beginTransaction();

        try {

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

            if ($otpStatus === 'off') {

                $user = User::updateOrCreate(['email' => $request->email], $request->except('password') + [
                    'remember_token' => null,
                    'role' => 'landlord',
                    'email_verified_at' => now(),
                    'password' => Hash::make($request->password),
                    'is_setupped' => 1,
                ]
                );

                session()->put('user_id', $user->id);
                session()->put('plan_id', $request->plan_id);

                DB::commit();

                Auth::login($user);

                return response()->json([
                    'message' => 'Account created successfully.',
                    'redirect' => route('payments-gateways.index', [
                        'plan_id' => $request->plan_id,
                        'landlord_id' => $user->id,
                    ]),
                ]);
            }

            $expireSeconds = $this->getOtpTimeInSeconds();
            $expire = now()->addSeconds($expireSeconds);

            $user = User::updateOrCreate(['email' => $request->email], $request->except('password') + [
                'remember_token' => $code,
                'role' => 'landlord',
                'email_verified_at' => $expire,
                'password' => Hash::make($request->password),
                'is_setupped' => 1,
            ]
            );

            session()->put('user_id', $user->id);
            session()->put('plan_id', $request->plan_id);

            if (env('QUEUE_MAIL')) {
                Mail::to($request->email)->queue(new WelcomeMail($data));
            } else {
                Mail::to($request->email)->send(new WelcomeMail($data));
            }

            DB::commit();

            return response()->json([
                'message' => 'An OTP code has been sent to your email. Please check and confirm.',
                'openModal' => true,
                'email' => $request->email,
                'otp_expiration' => $expireSeconds,
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong. Please contact the admin.',
            ], 403);
        }
    }

    public function getOtpTimeInSeconds()
    {
        $otpSettings = get_option('email-varification') ?? [];

        $otpStatus = $otpSettings['otp_status'] ?? 'off';

        if ($otpStatus === 'off') {
            return 0;
        }

        $time = $otpSettings['otp_expiration_time'] ?? null;
        $durationType = $otpSettings['otp_duration_type'] ?? 'minute';

        $defaultFromEnv = env('OTP_VISIBILITY_TIME', 3);

        if (empty($time)) {
            return $defaultFromEnv * 60;
        }

        return $durationType === 'minute' ? $time * 60 : (int) $time;
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

        if ($user->remember_token == $request->otp) {
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
                $duration = 0;
                if ($plan->duration_type === 'month') {
                    $duration = $plan->duration * 30;
                } elseif ($plan->duration_type === 'year') {
                    $duration = $plan->duration * 365;
                } elseif ($plan->duration_type === 'day') {
                    $duration = $plan->duration;
                }

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
                $redirect_url = route('payments-gateways.index', [
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
        $expire = now()->addSeconds(env('OTP_VISIBILITY_TIME') ?? 60);

        $data = [
            'code' => $code,
            'name' => $request->name,
        ];

        if (env('MAIL_USERNAME')) {
            if (env('QUEUE_MAIL')) {
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
