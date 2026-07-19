<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Sample data for local testing ONLY. Do not run in production.
     * Usage: php artisan db:seed --class=DemoDataSeeder
     *
     * Requires PermissionSeeder + AdminUserSeeder to have run first
     * (i.e. run `php artisan migrate --seed` before this).
     */
    public function run(): void
    {
        $this->call([
            CustomerSeeder::class,
            LicenseSeeder::class,
            BlacklistSeeder::class,
        ]);
    }
}
