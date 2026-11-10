<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Mail\WelcomeMail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class SigninController extends Controller
{
    public function maanSignIn(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'email' => 'required|email',
        ]);

        if (auth()->attempt($request->only('email', 'password'))) {
            $user = auth()->user();

            if ($user->role != 'landlord' && $user->role != 'tenant') {
                return response()->json([
                    'message' => 'You can not login as ' . $user->role . ' from the app!'
                ], 406);
            }

            if ($user->remember_token && !$user->is_setupped) { // If user didn't verify email

                $code = random_int(100000, 999999);
                $expire = now()->addMinutes(env('OTP_VISIBILITY_TIME') ?? 3);
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
                ], 201);
            }

            $data = [
                'id' => $user->id,
                'role' => $user->role,
                'name' => $user->name,
                'email' => $user->email,
                'is_setupped' => $user->is_setupped,
                'token' => $user->createToken('createToken')->plainTextToken,
            ];

            return response()->json([
                'message' => 'User login successfully!',
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'message' => 'Invalid email or password!'
            ], 401);
        }
    }

    public function maanSignOut(): JsonResponse
    {
        $currentToken = auth()->user()->currentAccessToken();

        if ($currentToken) {
            $currentToken->delete();

            return response()->json([
                'message' => __('Sign out successfully'),
            ]);
        } else {
            return response()->json([
                'message' => __('Unauthorized'),
            ], 401);
        }
    }

    public function userRefreshToken(): JsonResponse
    {

        if (auth()->user()->tokens()) {

            auth()->user()->currentAccessToken()->delete();
            $data['token'] = auth()->user()->createToken('createToken')->plainTextToken;
            return response()->json($data);
        } else {
            return response()->json([
                'message' => __('Unauthorized'),
            ], 401);
        }
    }
}
