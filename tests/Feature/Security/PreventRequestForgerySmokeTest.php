<?php

namespace Tests\Feature\Security;

use App\Http\Kernel;
use App\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery as FrameworkPreventRequestForgery;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use Tests\TestCase;

#[Group('smoke')]
#[Group('security')]
class PreventRequestForgerySmokeTest extends TestCase
{
    /**
     * F5 (spec 5.15): el stack global usa la clase renombrada de L13, no los
     * aliases deprecados. El bypass de CSRF en tests del framework impide
     * asertar 419 dentro de la suite; la evidencia de rechazo real se toma
     * contra `artisan serve` y se pega en el PR.
     */
    public function test_f5_web_group_uses_renamed_middleware(): void
    {
        $webGroup = $this->app->make(Kernel::class)->getMiddlewareGroups()['web'];

        $this->assertContains(PreventRequestForgery::class, $webGroup);
        $this->assertNotContains(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, $webGroup);
        $this->assertNotContains(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class, $webGroup);
    }

    public function test_f5_csrf_middleware_has_no_exemptions(): void
    {
        $reflection = new ReflectionClass(PreventRequestForgery::class);
        $defaults = $reflection->getProperty('except')->getDefaultValue();

        $this->assertSame([], $defaults);
    }

    public function test_f5_sanctum_points_to_renamed_middleware(): void
    {
        $this->assertSame(
            FrameworkPreventRequestForgery::class,
            config('sanctum.middleware.validate_csrf_token'),
        );
    }
}
