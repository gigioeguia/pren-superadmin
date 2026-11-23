<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * Dueño único de las tx de roles (spec-tx-legadas-services-fase5).
 *
 * D-FIX-role-update (spec 5.2): update ahora envuelve role + permissions
 * sync en una tx (antes eran dos escrituras sin tx — un crash entre ambas
 * dejaba el rol renombrado con permisos viejos). El happy path observable
 * no cambia (F15).
 */
class RoleService
{
    /**
     * @param  array{name: string, permissions: array<int, int>}  $data
     */
    public function store(array $data): Role
    {
        return DB::transaction(function () use ($data): Role {
            $role = Role::create([
                'name' => $data['name'],
            ]);

            $role->permissions()->sync($data['permissions']);

            return $role;
        });
    }

    /**
     * @param  array{name: string, permissions: array<int, int>}  $data
     */
    public function update(Role $role, array $data): void
    {
        DB::transaction(function () use ($role, $data): void {
            $role->update([
                'name' => $data['name'],
            ]);

            $role->permissions()->sync($data['permissions']);
        });
    }
}
