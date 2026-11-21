<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * Proxies confiables desde config (TRUSTED_PROXIES, Seg-23 / Fase 5 / 5.6);
     * '*' y CSV los resuelve el parent.
     *
     * @return array<int, string>|string|null
     */
    protected function proxies()
    {
        $proxies = config('app.trusted_proxies');

        return is_string($proxies) || is_array($proxies) ? $proxies : null;
    }
}
