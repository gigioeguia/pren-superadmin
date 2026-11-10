<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Mail\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /*
       |--------------------------------------------------------------------------
       | Password Reset Controller
       |--------------------------------------------------------------------------
       |
       | This controller is responsible for handling password reset emails and
       | includes a trait which assists in sending these notifications from
       | your application to your users. Feel free to explore this trait.
       |
       */
    //use SendsPasswordResetEmails;

    public function sendResetCode(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $expire = now()->addHour();
        $code = random_int(100000, 999999);
        $user = User::where('email', $request->email)->first();
        $user->update(['remember_token' => $code, 'email_verified_at' => $expire]);

        $data = [
            'code' => $code
        ];

        try {
            if (env('QUEUE_MAIL')) {
                Mail::to($request->email)->queue(new PasswordReset($data));
            } else {
                Mail::to($request->email)->send(new PasswordReset($data));
            }
            return response()->json([
                'message' => 'Password reset code has been sent to your email.',
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $code = random_int(100000, 999999);
        $expire = now()->addMinutes(env('OTP_VISIBILITY_TIME') ?? 3);
        $data = [
            'code' => $code,
        ];

        if (env('MAIL_USERNAME')) {
            if (env('QUEUE_MAIL')) {
                Mail::to($request->email)->queue(new PasswordReset($data));
            } else {
                Mail::to($request->email)->send(new PasswordReset($data));
            }
        } else {
            return response()->json([
                'message' => __('Mail service is not configured. Please contact your administrator.'),
            ], 406);
        }

        User::where('email', $request->email)->first()->update(['remember_token' => $code, 'email_verified_at' => $expire]);

        return response()->json([
            'message' => 'An otp code has been sent to your email. Please check and confirm.',
        ]);
    }
}
