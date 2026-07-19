<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VerificationResult;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\LicenseVerification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LicenseVerification>
 */
class LicenseVerificationFactory extends Factory
{
    protected $model = LicenseVerification::class;

    public function definition(): array
    {
        return [
            'license_id'            => License::factory(),
            'license_activation_id' => LicenseActivation::factory(),
            'result'                => VerificationResult::Success->value,
            'installation_id'       => 'INST-' . strtoupper(Str::random(24)),
            'normalized_domain'     => fake()->domainName(),
            'ip_address'            => fake()->ipv4(),
            'nonce'                 => bin2hex(random_bytes(16)),
            'payload_hash'          => hash('sha256', Str::random(40)),
            'latency_ms'            => fake()->numberBetween(5, 400),
            'verified_at'           => now(),
            'meta'                  => null,
        ];
    }

    public function failed(): static
    {
        return $this->state(fn () => ['result' => VerificationResult::Failed->value]);
    }

    public function killed(): static
    {
        return $this->state(fn () => ['result' => VerificationResult::Killed->value]);
    }
}
