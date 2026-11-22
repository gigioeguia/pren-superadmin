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
 * Allowlist temporal (decisor 2026-11-22, D1 escalonado): 54 rutas
 * referenciadas por vistas vivas cuyos métodos faltan desde 4.2 —
 * features rotas, mismo bug-clase que D2. El slice propio
 * "restauración CRUD 4.2" las implementa y las retira de aquí (la
 * segunda aserción fuerza ese retiro).
 */
class RouteMethodsReflectionTest extends TestCase
{
    /**
     * Rutas muertas conocidas con dueño (restauración CRUD 4.2).
     */
    private const RESTORATION_PENDING = [
        'admin.amenities.create',
        'admin.amenities.edit',
        'admin.expense-categories.create',
        'admin.expense-categories.edit',
        'admin.expense-categories.status',
        'admin.expenses.create',
        'admin.expenses.csv',
        'admin.expenses.edit',
        'admin.expenses.excel',
        'admin.facilities.create',
        'admin.facilities.edit',
        'admin.house-types.create',
        'admin.house-types.edit',
        'admin.income-categories.create',
        'admin.income-categories.edit',
        'admin.income-categories.status',
        'admin.incomes.create',
        'admin.incomes.csv',
        'admin.incomes.edit',
        'admin.incomes.excel',
        'admin.refund-reason.create',
        'admin.refund-reason.edit',
        'admin.security-deposits.csv',
        'admin.security-deposits.excel',
        'admin.security-deposits.filter',
        'admin.withdraw-methods.status',
        'landlord.amenities.create',
        'landlord.amenities.edit',
        'landlord.expense-categories.create',
        'landlord.expense-categories.delete-all',
        'landlord.expense-categories.edit',
        'landlord.expense-categories.status',
        'landlord.expenses.create',
        'landlord.expenses.csv',
        'landlord.expenses.edit',
        'landlord.expenses.excel',
        'landlord.facilities.create',
        'landlord.facilities.edit',
        'landlord.income-categories.create',
        'landlord.income-categories.delete-all',
        'landlord.income-categories.edit',
        'landlord.income-categories.status',
        'landlord.incomes.create',
        'landlord.incomes.csv',
        'landlord.incomes.edit',
        'landlord.incomes.excel',
        'landlord.labors.create',
        'landlord.labors.edit',
        'landlord.refund-request.csv',
        'landlord.refund-request.excel',
        'landlord.security-deposits.csv',
        'landlord.security-deposits.excel',
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
