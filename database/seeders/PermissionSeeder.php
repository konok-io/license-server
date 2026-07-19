<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Seeds the RBAC permissions & roles referenced by the Phase 3 policies.
     * Requires spatie/laravel-permission.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Customers
            'customers.view', 'customers.create', 'customers.update', 'customers.delete',
            // Licenses
            'licenses.view', 'licenses.create', 'licenses.update', 'licenses.delete',
            'licenses.kill', 'licenses.reset',
            // Blacklist
            'blacklists.view', 'blacklists.create', 'blacklists.update', 'blacklists.delete',
            // Audit
            'audit.view',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        // Roles from the approved architecture.
        $superAdmin = Role::findOrCreate('Super Admin', 'web');
        $manager    = Role::findOrCreate('License Manager', 'web');
        $support    = Role::findOrCreate('Support Agent', 'web');
        $auditor    = Role::findOrCreate('Auditor', 'web');

        $superAdmin->givePermissionTo(Permission::all());

        $manager->givePermissionTo([
            'customers.view', 'customers.create', 'customers.update',
            'licenses.view', 'licenses.create', 'licenses.update', 'licenses.kill', 'licenses.reset',
            'blacklists.view', 'blacklists.create', 'blacklists.update',
            'audit.view',
        ]);

        $support->givePermissionTo([
            'customers.view', 'licenses.view', 'licenses.reset',
            'blacklists.view', 'audit.view',
        ]);

        $auditor->givePermissionTo([
            'customers.view', 'licenses.view', 'blacklists.view', 'audit.view',
        ]);
    }
}
