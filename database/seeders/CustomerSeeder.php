<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // Deterministic demo customer for manual QA.
        Customer::query()->firstOrCreate(
            ['email' => 'demo@saudimanpower.sa'],
            [
                'name'      => 'Demo Customer',
                'company'   => 'MRH Software Demo Co.',
                'phone'     => '+966500000000',
                'country'   => 'SA',
                'is_active' => true,
            ]
        );

        // Bulk sample customers.
        Customer::factory()->count(15)->create();

        // A few inactive customers.
        Customer::factory()->count(3)->inactive()->create();
    }
}
