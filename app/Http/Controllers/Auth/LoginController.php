<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->filled('remember'))) {

            $request->session()->regenerate();

            $user = auth()->user();

            $redirect_url = url('/');

            if ($user->role === 'landlord') {
                $redirect_url = route('landlord.dashboard');

            } elseif ($user->role === 'tenant') {
                Auth::logout();

                return response()->json([
                    'message' => __('You cannot log in as a tenant.'),
                ], 403);

            } else {
                $role = Role::where('name', $user->role)->first();
                $first_permission = $role->permissions->pluck('name')->first();
                $page = explode('-', $first_permission, 2)[0];
                $routeName = 'admin.'.$page;

                // Para recursos como roles, usar la ruta de listado si existe.
                if (
                    ! Route::has($routeName) &&
                    Route::has($routeName.'.index')
                ) {
                    $routeName .= '.index';
                }

                $redirect_url = route($routeName);
            }

            return response()->json([
                'message' => __('Logged In Successfully'),
                'remember' => $request->filled('remember') ? 1 : 0,
                'redirect' => $redirect_url,
            ]);
        }

        return response()->json([
            'message' => __('Invalid Email or Password!'),
        ], 401);
    }

    public function logout()
    {
        Session::forget('Success');
        Auth::logout();

        return redirect('/login');
    }
}
