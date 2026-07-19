<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ActivationStatus;
use App\Enums\AuditEvent;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Support\AuditLogger;
use Illuminate\Console\Command;

/**
 * Revokes activations that have not verified in far longer than their license's
 * verification interval (default: interval × 3 + grace). This frees activation
 * slots held by installations that silently went offline.
 */
class PurgeStaleActivations extends Command
{
    protected $signature = 'license:purge-stale {--multiplier=3} {--dry-run}';

    protected $description = 'Revoke activations that stopped verifying long ago and free their slots.';

    public function handle(): int
    {
        $multiplier = max(2, (int) $this->option('multiplier'));
        $purged = 0;
        $touchedLicenses = [];

        LicenseActivation::query()
            ->where('status', ActivationStatus::Active->value)
            ->with('license:id,license_key_prefix,verification_interval_hours,grace_days')
            ->chunkById(200, function ($activations) use ($multiplier, &$purged, &$touchedLicenses): void {
                foreach ($activations as $activation) {
                    $license = $activation->license;
                    if ($license === null) {
                        continue;
                    }

                    $staleHours = ($license->verification_interval_hours * $multiplier)
                        + ($license->grace_days * 24);

                    $last = $activation->last_verified_at ?? $activation->activated_at;
                    if ($last === null || $last->diffInHours(now()) < $staleHours) {
                        continue;
                    }

                    if ($this->option('dry-run')) {
                        $this->line("Would purge: {$activation->installation_id} (last seen {$last->diffForHumans()})");
                        $purged++;
                        continue;
                    }

                    $activation->forceFill([
                        'status'     => ActivationStatus::Expired->value,
                        'revoked_at' => now(),
                    ])->save();

                    $touchedLicenses[$license->id] = true;
                    $purged++;
                }
            });

        // Recompute activation counters for affected licenses.
        if (! $this->option('dry-run') && $touchedLicenses !== []) {
            License::query()->whereIn('id', array_keys($touchedLicenses))->get()
                ->each(function (License $license): void {
                    $license->forceFill([
                        'activation_count' => $license->activations()->active()->count(),
                    ])->save();

                    AuditLogger::record(
                        AuditEvent::ActivationRevoked,
                        $license,
                        'Stale activations purged by scheduler',
                        actorType: 'system',
                    );
                });
        }

        $this->info(($this->option('dry-run') ? '[dry-run] ' : '') . "Purged {$purged} stale activation(s).");

        return self::SUCCESS;
    }
}
