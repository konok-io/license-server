<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\License;
use App\Models\LicenseBlacklist;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LicenseBlacklist>
 */
class LicenseBlacklistFactory extends Factory
{
    protected $model = LicenseBlacklist::class;

    public function definition(): array
    {
        return [
            'uuid'              => (string) Str::uuid(),
            'license_id'        => License::factory(),
            'installation_id'   => 'INST-' . strtoupper(Str::random(24)),
            'normalized_domain' => fake()->domainName(),
            'ip_address'        => fake()->ipv4(),
            'license_key_hash'  => hash('sha256', Str::random(40)),
            'reason'            => fake()->randomElement([
                'License piracy detected',
                'Chargeback / fraud',
                'Terms of service violation',
                'Tampering attempt',
            ]),
            'is_active'         => true,
            'created_by'        => null,
            'created_by_name'   => 'Security Team',
            'blacklisted_at'    => now(),
            'lifted_at'         => null,
            'meta'              => null,
        ];
    }

    public function lifted(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
            'lifted_at' => now(),
        ]);
    }
}
