<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ActivationLog;
use App\Models\License;
use App\Models\LicenseActivation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ActivationLog>
 */
class ActivationLogFactory extends Factory
{
    protected $model = ActivationLog::class;

    public function definition(): array
    {
        return [
            'license_id'            => License::factory(),
            'license_activation_id' => LicenseActivation::factory(),
            'action'                => fake()->randomElement(['activate', 'deactivate', 'reactivate']),
            'success'               => true,
            'reason'                => null,
            'installation_id'       => 'INST-' . strtoupper(Str::random(24)),
            'normalized_domain'     => fake()->domainName(),
            'server_type'           => fake()->randomElement(['localhost', 'domain', 'vps']),
            'ip_address'            => fake()->ipv4(),
            'user_agent'            => 'MRHSoftwareERP/1.0',
            'request_payload'       => ['source' => 'factory'],
        ];
    }

    public function denied(): static
    {
        return $this->state(fn () => [
            'action'  => 'denied',
            'success' => false,
            'reason'  => 'Activation limit reached',
        ]);
    }
}
