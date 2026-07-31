<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ActivationLog;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\LicenseVerification;
use App\Models\VerificationLog;
use App\Enums\AuditEvent;
use Illuminate\Database\Seeder;

class LicenseSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::query()->where('is_active', true)->get();

        $customers->each(function (Customer $customer): void {
            // Each active customer gets 1–3 licenses across types.
            License::factory()
                ->count(fake()->numberBetween(1, 3))
                ->for($customer)
                ->create()
                ->each(function (License $license): void {
                    $this->seedActivationsFor($license);
                });
        });

        // Some edge-case licenses on the demo customer.
        $demo = Customer::query()->where('email', 'demo@mrhsoftware.com')->first();

        if ($demo !== null) {
            License::factory()->for($demo)->localhost()->create();
            License::factory()->for($demo)->vps()->create();
            License::factory()->for($demo)->killed()->create();
            License::factory()->for($demo)->expired()->create();
            License::factory()->for($demo)->suspended()->create();
        }
    }

    private function seedActivationsFor(License $license): void
    {
        $count = min($license->max_activations, fake()->numberBetween(0, $license->max_activations));

        for ($i = 0; $i < $count; $i++) {
            $activation = LicenseActivation::factory()->for($license)->create();

            ActivationLog::factory()->for($license)->create([
                'license_activation_id' => $activation->id,
                'action'                => 'activate',
                'installation_id'       => $activation->installation_id,
                'normalized_domain'     => $activation->normalized_domain,
            ]);

            // A handful of verifications per activation.
            LicenseVerification::factory()
                ->count(fake()->numberBetween(1, 5))
                ->for($license)
                ->create(['license_activation_id' => $activation->id])
                ->each(function (LicenseVerification $verification) use ($license): void {
                    VerificationLog::factory()->for($license)->create([
                        'license_verification_id' => $verification->id,
                    ]);
                });

            AuditLog::factory()->create([
                'event'          => AuditEvent::LicenseActivated->value,
                'auditable_type' => License::class,
                'auditable_id'   => $license->id,
                'actor_type'     => 'api_client',
                'description'    => "Activation created for license #{$license->id}",
            ]);
        }

        // Keep denormalized counters honest.
        $license->update([
            'activation_count' => $license->activations()->active()->count(),
        ]);
    }
}
