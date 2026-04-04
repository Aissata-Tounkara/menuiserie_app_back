<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $systemPermissions = [
            'view-logs',
            'manage-users',
            'force-logout',
        ];

        $businessPermissions = [
            'create-clients', 'edit-clients', 'delete-clients',
            'create-devis', 'edit-devis', 'delete-devis',
            'create-commandes', 'edit-commandes', 'delete-commandes',
            'create-factures', 'edit-factures', 'delete-factures',
            'create-depenses', 'edit-depenses', 'delete-depenses',
            'create-articles', 'edit-articles', 'delete-articles',
            'create-mouvements', 'edit-mouvements', 'delete-mouvements',
        ];

        $permissions = [...$systemPermissions, ...$businessPermissions];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions($permissions);

        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $employeeRole->syncPermissions($businessPermissions);
    }
}
