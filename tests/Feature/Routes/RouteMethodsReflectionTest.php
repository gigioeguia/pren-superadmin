<?php

namespace Tests\Feature\Routes;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * U3 del spec-controladores-residuales-fase5 (paraguas §6): reflexión
 * permanente rutas↔métodos. Toda ruta declarada debe apuntar a un
 * método existente — el bug de las rutas muertas de 4.2 no puede
 * volver a entrar sin romper CI.
 *
 * Allowlist vacía desde el cierre del spec 5.17 (2026-11-22): las 54
 * entradas fueron restauradas (22, spec de restauración) o eliminadas
 * (32, verbos resource zombis que nunca tuvieron método). El guard
 * queda bidireccional y permanente: una ruta muerta nueva rompe CI
 * hasta que se restaure (spec) o se elimine (ruta).
 */
class RouteMethodsReflectionTest extends TestCase
{
    /**
     * Rutas muertas conocidas (restauración/limpieza CRUD 4.2 — cerrado).
     */
    private const RESTORATION_PENDING = [];

    public function test_u3_every_declared_route_resolves_to_an_existing_method(): void
    {
        $dead = [];

        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();

            if (! str_contains($action, '@')) {
                continue;
            }

            [$class, $method] = explode('@', $action, 2);

            if (! str_starts_with($class, 'App\\Http\\Controllers\\')) {
                continue;
            }

            // La clase base Controller no es acción terminal válida.
            if ($class === Controller::class) {
                $dead[] = $route->getName() ?? $route->uri();

                continue;
            }

            if (! class_exists($class) || ! method_exists($class, $method)) {
                $dead[] = $route->getName() ?? $route->uri();
            }
        }

        $unexpected = array_values(array_diff($dead, self::RESTORATION_PENDING));
        sort($unexpected);
        $this->assertSame([], $unexpected, sprintf(
            "Rutas muertas nuevas (metodo inexistente) fuera del allowlist: [%s]\n%s",
            implode(', ', $unexpected),
            'Si son features referenciadas por vistas, crear spec de restauracion; si no, eliminar la ruta.'
        ));

        $alreadyRestored = array_values(array_diff(self::RESTORATION_PENDING, $dead));
        sort($alreadyRestored);
        $this->assertSame([], $alreadyRestored, sprintf(
            'Rutas del allowlist ya no estan muertas — retirarlas de RESTORATION_PENDING: [%s]',
            implode(', ', $alreadyRestored)
        ));
    }
}
