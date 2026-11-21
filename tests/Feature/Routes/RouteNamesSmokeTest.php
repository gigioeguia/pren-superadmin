<?php

namespace Tests\Feature\Routes;

use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * F14–F18 del spec-typos-rutas-fase5: guards de higiene de la tabla de
 * rutas — unicidad de nombres, 0 nombres de prefijo desnudo, rename
 * pineado, colisión maintenance resuelta y salud de fuente.
 *
 * @group smoke
 */
class RouteNamesSmokeTest extends TestCase
{
    public function test_f14_route_names_are_unique(): void
    {
        // F14: unicidad en la colección plana. Nombres con '::' son del
        // paquete LaravelInstaller (codeVerifyProcess x2 por diseño vendor,
        // preexistente) — el guard cubre las rutas de la app.
        $names = collect(app('router')->getRoutes())
            ->map(fn ($route) => $route->getName())
            ->filter(fn ($name) => $name !== null && ! str_contains($name, '::'));

        $duplicates = $names->duplicates();

        $this->assertCount(0, $duplicates, "Duplicated route names: {$duplicates->implode(', ')}");
    }

    public function test_f15_no_bare_group_prefix_names(): void
    {
        // F15: ninguna ruta hereda el prefijo de grupo como nombre completo.
        $names = collect(app('router')->getRoutes())
            ->map(fn ($route) => $route->getName())
            ->filter();

        $this->assertFalse($names->contains('api'), 'Route named [api] (bare group prefix) is registered.');
        $this->assertFalse($names->contains('admin.'), 'Route named [admin.] (bare group prefix) is registered.');
    }

    public function test_f16_read_notifications_rename_is_pinned(): void
    {
        // F16: apiread-notifications registrada; URI typo inexistente.
        $route = app('router')->getRoutes()->getByName('apiread-notifications');

        $this->assertNotNull($route);
        $this->assertSame('api/v1/read-notifications', $route->uri());
        $this->assertContains('GET', $route->methods());

        $typoUris = collect(app('router')->getRoutes())
            ->map(fn ($r) => $r->uri())
            ->filter(fn ($uri) => str_contains($uri, 'read-nitifications'));

        $this->assertCount(0, $typoUris, 'The read-nitifications URI is still registered.');
    }

    public function test_f17_maintenance_index_collision_is_resolved(): void
    {
        // F17: landlord/tenant ya no comparten nombre; update del tenant
        // intacta (DemoMode referencia apimaintenance.update).
        $landlord = app('router')->getRoutes()->getByName('apilandlord-maintenance.index');
        $tenant = app('router')->getRoutes()->getByName('apitenant-maintenance.index');
        $update = app('router')->getRoutes()->getByName('apimaintenance.update');

        $this->assertNotNull($landlord);
        $this->assertNotNull($tenant);
        $this->assertSame('api/v1/landlord/maintenance', $landlord->uri());
        $this->assertSame('api/v1/tenant/maintenance', $tenant->uri());
        $this->assertNotNull($update);
    }

    public function test_f18_source_hygiene(): void
    {
        // F18: higiene de fuente — typos y registro duplicado fuera.
        $this->assertSame(0, substr_count(file_get_contents(base_path('routes/api.php')), 'read-nitifications'));
        $this->assertSame(1, substr_count(file_get_contents(base_path('routes/admin.php')), 'income-categories/delete-all'));
        $this->assertSame(0, substr_count(file_get_contents(base_path('phpstan-baseline.neon')), 'NotifyContrller'));

        $notifyHits = 0;
        foreach (Finder::create()->in(base_path('app'))->name('*.php')->files() as $file) {
            $notifyHits += substr_count($file->getContents(), 'NotifyContrller');
        }
        $this->assertSame(0, $notifyHits);

        $demoMode = file_get_contents(base_path('app/Http/Middleware/DemoMode.php'));
        $this->assertSame(0, substr_count($demoMode, 'admin..'));
        $this->assertSame(0, substr_count($demoMode, 'subscription-reports'));
    }
}
