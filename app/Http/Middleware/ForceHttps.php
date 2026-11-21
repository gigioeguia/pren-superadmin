<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceHttps
{
    /**
     * Seg-23 (Fase 5 / 5.6): redirect http→https (302) tras la válvula
     * FORCE_HTTPS (default off). Corre tras TrustProxies para que
     * request->secure() refleje X-Forwarded-Proto con proxies confiables.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (config('app.force_https') && ! $request->secure()) {
            return redirect()->to(
                str_replace('http://', 'https://', $request->fullUrl())
            );
        }

        return $next($request);
    }
}
