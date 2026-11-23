<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('/') || $request->is('login')) {
            if (! file_exists(storage_path('installed'))) {
                return redirect('install');
            }
        }

        if ($request->has('lang')) {
            $lang = $request->get('lang');
            $locale = is_string($lang) ? $lang : (string) config('app.locale');
            app()->setLocale($locale);
            session(['locale' => $locale]);
        } elseif (session()->has('locale')) {
            app()->setLocale(session('locale'));
        } else {
            app()->setLocale(config('app.locale'));
        }

        return $next($request);
    }
}
