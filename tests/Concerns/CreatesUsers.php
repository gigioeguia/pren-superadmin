<?php

namespace Tests\Concerns;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait CreatesUsers
{
    protected function createLandlord(array $attributes = []): User
    {
        return User::factory()->landlord()->create($attributes);
    }

    protected function createTenant(array $attributes = []): User
    {
        return User::factory()->tenant()->create($attributes);
    }

    protected function createAdmin(array $attributes = []): User
    {
        $admin = User::factory()->admin()->create($attributes);
        $admin->assignRole($this->ensureSpatieRole('admin'));

        return $admin;
    }

    protected function createSuperadmin(array $attributes = []): User
    {
        $superadmin = User::factory()->superadmin()->create($attributes);
        $superadmin->assignRole($this->ensureSpatieRole('superadmin'));

        return $superadmin;
    }

    /**
     * Garantiza rol Spatie con dashboard-read.
     *
     * PermissionSeeder lo crea en la DB de testing; firstOrCreate
     * como fallback si el test no corrio el seeder (GET /admin
     * exige permission:dashboard-read).
     */
    private function ensureSpatieRole(string $name): Role
    {
        $role = Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);

        $role->givePermissionTo(
            Permission::firstOrCreate(['name' => 'dashboard-read', 'guard_name' => 'web'])
        );

        return $role;
    }
}
