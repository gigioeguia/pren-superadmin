<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                if ($user->role === 'admin' || $user->role === 'superadmin') {
                    $role = \Spatie\Permission\Models\Role::where('name', $user->role)->first();

                    if ($role && $role->permissions->isNotEmpty()) {
                        $first_permission = $role->permissions->pluck('name')->first(); // e.g., 'user-list'
                        $page = explode('-', $first_permission); // ['user', 'list']

                        if (!empty($page[0])) {
                            return redirect()->route('admin.' . $page[0])
                                ->with('warning', 'You are already logged in!');
                        }
                    }

                    // fallback for admin with no permissions
                    return redirect()->route('admin.dashboard')
                        ->with('warning', 'You are already logged in!');
                }

                if ($user->role === 'landlord') {
                    return redirect()->route('landlord.dashboard')
                        ->with('warning', 'You are already logged in!');
                }

                // Optional: unknown role fallback
                Auth::logout();
                return redirect()->route('login')->with('warning', 'Unknown role. Please contact support.');
            }
        }

        return $next($request);
    }
}
