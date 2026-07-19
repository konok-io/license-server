<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\License;
use App\Models\LicenseReset;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LicenseReset>
 */
class LicenseResetFactory extends Factory
{
    protected $model = LicenseReset::class;

    public function definition(): array
    {
        return [
            'uuid'                => (string) Str::uuid(),
            'license_id'          => License::factory(),
            'reason'              => fake()->randomElement([
                'Hardware migration',
                'Server relocation',
                'Customer request',
                'Support intervention',
            ]),
            'activations_cleared' => fake()->numberBetween(1, 3),
            'old_rsa_key_version' => 'v1',
            'new_rsa_key_version' => 'v2',
            'performed_by'        => null,
            'performed_by_name'   => 'System Admin',
            'ip_address'          => fake()->ipv4(),
            'reset_at'            => now(),
            'meta'                => null,
        ];
    }
}
