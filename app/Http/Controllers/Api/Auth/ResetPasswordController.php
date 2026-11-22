<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class ResetPasswordController extends Controller
{
    public function userResetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|exists:users,email',
            'otp' => 'required',
            'password' => Password::required(),
        ]);

        $user = User::where('email', $request->email)->first();

        if (
            ! $user
            || ! $user->remember_token
            || ! $user->email_verified_at
            || now()->greaterThan($user->email_verified_at)
            || ! hash_equals((string) $user->remember_token, (string) $request->otp)
        ) {
            return response()->json([
                'message' => 'Invalid or expired OTP code.',
            ], 422);
        }

        $user->forceFill([
            'password' => bcrypt($request->password),
            'remember_token' => null,
            'email_verified_at' => now(),
        ])->save();

        return response()->json([
            'message' => 'Your password has been changed!',
        ]);

    }
}
