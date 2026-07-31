<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\License;
use App\Models\LicenseVerification;
use App\Models\VerificationLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<VerificationLog>
 */
class VerificationLogFactory extends Factory
{
    protected $model = VerificationLog::class;

    public function definition(): array
    {
        return [
            'license_id'              => License::factory(),
            'license_verification_id' => LicenseVerification::factory(),
            'result'                  => 'success',
            'kill_directive'          => false,
            'installation_id'         => 'INST-' . strtoupper(Str::random(24)),
            'normalized_domain'       => fake()->domainName(),
            'ip_address'              => fake()->ipv4(),
            'user_agent'              => 'MRHSoftwareERP/1.0',
            'nonce'                   => bin2hex(random_bytes(16)),
            'latency_ms'              => fake()->numberBetween(5, 400),
            'request_payload'         => ['source' => 'factory'],
            'response_payload'        => ['valid' => true],
        ];
    }

    public function killDirective(): static
    {
        return $this->state(fn () => [
            'result'         => 'killed',
            'kill_directive' => true,
            'response_payload' => ['valid' => false, 'action' => 'KILL'],
        ]);
    }
}
