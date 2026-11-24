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

        // Detrás de un proxy confiable (p. ej. Render LB) aceptamos el host
        // reenviado cuando TRUSTED_PROXIES está configurado; evita 400 por
        // desalineación de APP_URL en entornos de prueba/demo.
        if (env('TRUSTED_PROXIES')) {
            $hosts[] = '*';
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
