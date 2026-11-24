<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Patrones confiables: APP_URL + subdominios siempre; dev hosts solo en
     * entornos no-productivos. Despliegues multi-dominio requieren editar aquí.
     *
     * @return array<int, string|null>
     */
    public function hosts(): array
    {
        $hosts = [$this->allSubdomainsOfApplicationUrl()];

        // Detrás de un proxy confiable (p. ej. Render LB) confiamos en el host
        // reenviado por el proxy (X-Forwarded-Host) para evitar 400 por
        // desalineación de APP_URL en entornos de prueba/demo. No usar '*',
        // porque Symfony valida los patrones con preg_match y '*' no es válido.
        if (env('TRUSTED_PROXIES')) {
            try {
                $forwardedHost = $this->app->make('request')->getHost();
                if (is_string($forwardedHost) && $forwardedHost !== '') {
                    $hosts[] = $forwardedHost;
                }
            } catch (\Throwable $e) {
                // Ignorar: caerá al patrón de APP_URL.
            }
        }

        if ($this->app->environment('local', 'testing')) {
            $hosts[] = 'localhost';
            $hosts[] = '127.0.0.1';
            $hosts[] = '::1';
        }

        return $hosts;
    }

    /**
     * Override de testabilidad (Seg-21 / 5.4): activo también bajo PHPUnit
     * (el vendor lo salta); `local` conserva el no-op del framework.
     */
    protected function shouldSpecifyTrustedHosts(): bool
    {
        return ! $this->app->environment('local');
    }
}
