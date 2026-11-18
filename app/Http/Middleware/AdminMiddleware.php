<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if ($user instanceof User && $user->hasAnyRole(['admin', 'superadmin'])) {
            return $next($request);
        }

        return redirect()->route('login');
    }
}
