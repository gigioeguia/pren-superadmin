<?php


namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rolesStructure = [
            'Super Admin' => [
                'dashboard' => 'r',
                'plans' => 'r,c,u,d',
                'users' => 'r,c,u,d',
                'properties' => 'r,c,u,d',
                'landlords' => 'r,c,u,d',
                'tenants' => 'r,c,u,d',
                'transactions' => 'r',
                'withdraw-methods' => 'r,c,u,d',
                'withdraw-request' => 'r,u',
                'subscriptions' => 'r,u',
                'security-deposit' => 'r,u',
                'rent-payment' => 'r,u',
                'rent-payment-generate' => 'r,c',
                'payments' => 'r,c,u,d',
                'reports' => 'r',
                'messages' => 'r,d',
                'refund_reasons' => 'r,c,u,d',
                'expenses' => 'r,c,u,d',
                'expense-categories' => 'r,c,u,d',
                'income-categories' => 'r,c,u,d',
                'incomes' => 'r,c,u,d',
                'house-types' => 'r,c,u,d',
                'facilities' => 'r,c,u,d',
                'amenities' => 'r,c,u,d',
                'reminder-message' => 'r,c,u,d',
                'app-settings' => 'r,u',
                'staffs' => 'r,c,u,d',
                'supports' => 'r,c',
                'profile' => 'r,u',

                // settings
                'currencies' => 'r,c,u,d',
                'settings' => 'r,u',
                'roles' => 'r,c,u,d',
                'permissions' => 'r,c',
                'notifications' => 'r',
                'payment-gateway' => 'r,u',
                'service-charge' => 'r,u',
                'term-conditions' => 'r,u',
                'privacy-policy' => 'r,u',
                'contact-us' => 'r,u',
                'about-us' => 'r,u',
                'profile' => 'r,u',
                'languages' => 'r,c,u,d',
            ],

            'Admin' => [
                'dashboard' => 'r',
                'plans' => 'r,c,u,d',
                'users' => 'r,c,u,d',
                'properties' => 'r,c,u,d',
                'landlords' => 'r,c,u,d',
                'tenants' => 'r,c,u,d',
                'transactions' => 'r',
                'withdraw-methods' => 'r,c,u,d',
                'withdraw-request' => 'r,u',
                'subscriptions' => 'r,u',
                'security-deposit' => 'r,u',
                'rent-payment' => 'r,u',
                'rent-payment-generate' => 'r,c',
                'payments' => 'r,c,u,d',
                'reports' => 'r',
                'messages' => 'r,d',
                'refund_reasons' => 'r,c,u,d',
                'expenses' => 'r,c,u,d',
                'expense-categories' => 'r,c,u,d',
                'income-categories' => 'r,c,u,d',
                'incomes' => 'r,c,u,d',
                'house-types' => 'r,c,u,d',
                'facilities' => 'r,c,u,d',
                'amenities' => 'r,c,u,d',
                'reminder-message' => 'r,c,u,d',
                'app-settings' => 'r,u',
                'staffs' => 'r,c,u,d',
                'supports' => 'r,c',
                'profile' => 'r,u',

                // settings
                'currencies' => 'r,c,u,d',
                'settings' => 'r,u',
                'roles' => 'r,c,u,d',
                'permissions' => 'r,c',
                'notifications' => 'r',
                'payment-gateway' => 'r,u',
                'service-charge' => 'r,u',
                'term-conditions' => 'r,u',
                'privacy-policy' => 'r,u',
                'contact-us' => 'r,u',
                'about-us' => 'r,u',
                'profile' => 'r,u',
                'languages' => 'r,c,u,d',
            ]
        ];

        foreach ($rolesStructure as $key => $modules) {
            // Create a new role
            $role = Role::firstOrCreate([
                'name' => str($key)->remove(' ')->lower(),
                'guard_name' => 'web',
            ]);
            $permissions = [];

            $this->command->info('Creating Role ' . strtoupper($key));

            // Reading role permission modules
            foreach ($modules as $module => $value) {

                foreach (explode(',', $value) as $perm) {

                    $permissionValue = $this->permissionMap()->get($perm);

                    $permissions[] = Permission::firstOrCreate([
                        'name' => $module . '-' . $permissionValue,
                        'guard_name' => 'web',
                    ])->id;

                    $this->command->info('Creating Permission to ' . $permissionValue . ' for ' . $module);
                }
            }

            // Attach all permissions to the role
            $role->permissions()->sync($permissions);

            $this->command->info("Creating '{$key}' user");
            // Create default user for each role
            $generated_role = str($key)->remove(' ')->lower();
            $user = User::create([
                'role' => $generated_role,
                'name' => ucwords(str_replace('_', ' ', $key)),
                'password' => bcrypt($generated_role),
                'email' => $generated_role . '@acnoo.com',
                'plan_subscribe_id' => $generated_role == 'landlord' ? 1 : NULL,
                'will_expire' => $generated_role == 'landlord' ? now()->addDays(30) : NULL,
                'email_verified_at' => now(),
            ]);

            $user->assignRole($role);
        }
    }

    private function permissionMap()
    {
        return collect([
            'c' => 'create',
            'r' => 'read',
            'u' => 'update',
            'd' => 'delete',
            's' => 'status',
            'f' => 'folder',
            'l' => 'list',
        ]);
    }
}
