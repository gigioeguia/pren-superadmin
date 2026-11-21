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
        return [
            $this->allSubdomainsOfApplicationUrl(),
            ...($this->app->environment('local', 'testing') ? ['localhost', '127.0.0.1', '::1'] : []),
        ];
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
