<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Creates the initial Super Admin login. Change the password immediately
     * after first login in production.
     */
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@konok.io')],
            [
                'name' => 'Super Admin',
                'password' => Hash::make(env('ADMIN_PASSWORD', '@rsm@k@1A')),
                'email_verified_at' => now(),
            ]
        );

        // PermissionSeeder must run first (it creates the roles).
        if (! $admin->hasRole('Super Admin')) {
            $admin->assignRole('Super Admin');
        }
    }
}
