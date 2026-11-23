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
 * Allowlist temporal (decisor 2026-11-22, D1 escalonado): quedan 32 rutas
 * create/edit declaradas-sin-método desde el import inicial (verbos resource
 * zombis; las 22 view-referenciadas ya fueron restauradas por el spec 5.17).
 * El slice de limpieza de ese mismo spec las retira de aquí (la segunda
 * aserción fuerza ese retiro).
 */
class RouteMethodsReflectionTest extends TestCase
{
    /**
     * Rutas muertas conocidas con dueño (limpieza create/edit, spec 5.17).
     */
    private const RESTORATION_PENDING = [
        'admin.amenities.create',
        'admin.amenities.edit',
        'admin.expense-categories.create',
        'admin.expense-categories.edit',
        'admin.expenses.create',
        'admin.expenses.edit',
        'admin.facilities.create',
        'admin.facilities.edit',
        'admin.house-types.create',
        'admin.house-types.edit',
        'admin.income-categories.create',
        'admin.income-categories.edit',
        'admin.incomes.create',
        'admin.incomes.edit',
        'admin.refund-reason.create',
        'admin.refund-reason.edit',
        'landlord.amenities.create',
        'landlord.amenities.edit',
        'landlord.expense-categories.create',
        'landlord.expense-categories.edit',
        'landlord.expenses.create',
        'landlord.expenses.edit',
        'landlord.facilities.create',
        'landlord.facilities.edit',
        'landlord.income-categories.create',
        'landlord.income-categories.edit',
        'landlord.incomes.create',
        'landlord.incomes.edit',
        'landlord.labors.create',
        'landlord.labors.edit',
        'landlord.withdraw-method.create',
        'landlord.withdraw-method.edit',
    ];

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
