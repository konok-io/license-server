<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ActivationStatus;
use App\Models\License;
use App\Models\LicenseActivation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LicenseActivation>
 */
class LicenseActivationFactory extends Factory
{
    protected $model = LicenseActivation::class;

    public function definition(): array
    {
        $domain = fake()->domainName();

        return [
            'uuid'              => (string) Str::uuid(),
            'license_id'        => License::factory(),
            'installation_id'   => 'INST-' . strtoupper(Str::random(24)),
            'fingerprint_hash'  => hash('sha256', Str::random(40)),
            'domain'            => $domain,
            'normalized_domain' => Str::lower($domain),
            'is_wildcard'       => false,
            'server_type'       => fake()->randomElement(['localhost', 'domain', 'vps']),
            'ip_address'        => fake()->ipv4(),
            'os_info'           => fake()->randomElement(['Windows Server 2022', 'Ubuntu 22.04', 'CentOS 7']),
            'user_agent'        => 'SaudiManpowerERP/1.0',
            'status'            => ActivationStatus::Active->value,
            'activated_at'      => now(),
            'last_verified_at'  => now(),
            'revoked_at'        => null,
            'meta'              => null,
        ];
    }

    public function revoked(): static
    {
        return $this->state(fn () => [
            'status'     => ActivationStatus::Revoked->value,
            'revoked_at' => now(),
        ]);
    }

    public function localhost(): static
    {
        return $this->state(fn () => [
            'server_type'       => 'localhost',
            'domain'            => 'localhost',
            'normalized_domain' => 'localhost',
            'ip_address'        => '127.0.0.1',
        ]);
    }
}
