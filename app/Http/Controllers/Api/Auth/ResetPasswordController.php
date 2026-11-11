<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    public function userResetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|exists:users,email',
            'password' => ['required', 'min:4'],
        ]);

        $user = User::where('email', $request->email)->first();

        $user->update([
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'message' => 'Your password has been changed!',
        ]);

    }
}
