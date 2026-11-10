<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Models\Api\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class ResetPasswordController extends Controller
{
    public function userResetPassword(Request $request) : JsonResponse
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
