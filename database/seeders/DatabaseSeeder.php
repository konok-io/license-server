<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Production seed: RBAC (roles/permissions) + the initial admin user only.
     * NO demo data — the system starts empty and you add real customers/licenses.
     *
     * To load sample/demo data for testing instead, run:
     *   php artisan db:seed --class=DemoDataSeeder
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            AdminUserSeeder::class,
            SiteSettingSeeder::class,
        ]);
    }
}
