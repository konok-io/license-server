<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LicenseStatus;
use App\Enums\LicenseType;
use App\Models\Customer;
use App\Models\License;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<License>
 */
class LicenseFactory extends Factory
{
    protected $model = License::class;

    public function definition(): array
    {
        // Simulated raw key (never persisted in plaintext).
        $rawKey = 'SLS-' . strtoupper(Str::random(4)) . '-'
            . strtoupper(Str::random(4)) . '-'
            . strtoupper(Str::random(4)) . '-'
            . strtoupper(Str::random(4));

        $issuedAt = fake()->dateTimeBetween('-6 months', 'now');

        return [
            'uuid'                        => (string) Str::uuid(),
            'customer_id'                 => Customer::factory(),
            'license_key_encrypted'       => $rawKey, // 'encrypted' cast handles at-rest encryption
            'license_key_hash'            => hash('sha256', $rawKey),
            'license_key_prefix'          => Str::substr($rawKey, 0, 8),
            'product'                     => 'saudi-manpower-erp',
            'version'                     => fake()->randomElement(['1.0.0', '1.2.0', '2.0.0']),
            'type'                        => fake()->randomElement(LicenseType::cases())->value,
            'status'                      => LicenseStatus::Active->value,
            'max_activations'             => fake()->randomElement([1, 1, 1, 3, 5]),
            'activation_count'            => 0,
            'rsa_key_version'             => 'v1',
            'rsa_signature'               => base64_encode(random_bytes(32)),
            'kill_switch'                 => false,
            'grace_days'                  => 3,
            'verification_interval_hours' => 24,
            'issued_at'                   => $issuedAt,
            'starts_at'                   => $issuedAt,
            'expires_at'                  => (clone $issuedAt)->modify('+1 year'),
            'last_verified_at'            => null,
            'killed_at'                   => null,
            'features'                    => ['modules' => ['payroll', 'attendance', 'recruitment']],
            'meta'                        => null,
        ];
    }

    public function localhost(): static
    {
        return $this->state(fn () => ['type' => LicenseType::Localhost->value]);
    }

    public function domain(): static
    {
        return $this->state(fn () => ['type' => LicenseType::Domain->value]);
    }

    public function vps(): static
    {
        return $this->state(fn () => ['type' => LicenseType::Vps->value]);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => LicenseStatus::Pending->value]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => LicenseStatus::Suspended->value]);
    }

    public function killed(): static
    {
        return $this->state(fn () => [
            'status'      => LicenseStatus::Killed->value,
            'kill_switch' => true,
            'killed_at'   => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status'     => LicenseStatus::Expired->value,
            'expires_at' => now()->subDays(5),
        ]);
    }
}
