<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ActivationStatus;
use App\Enums\AuditEvent;
use App\Enums\LicenseStatus;
use App\Models\ActivationLog;
use App\Models\Customer;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Support\AuditLogger;
use App\Support\DomainNormalizer;
use Illuminate\Support\Facades\DB;

/**
 * Unified control layer for every enforcement action in the system:
 * remote kill, suspend, and targeted disable of customers, domains, and
 * installations. Each operation is transactional, revokes the right
 * activations, refreshes counters, logs to activation_logs, and writes an
 * immutable audit entry. Enforcement takes effect on the next client verify.
 */
class LicenseControlService
{
    public function __construct(
        private readonly BlacklistService $blacklist,
    ) {
    }

    /* ================================================================
     |  Remote Kill Switch
     * ================================================================ */

    /**
     * Engage the remote kill switch for a single license. All active
     * activations are revoked; the next verify returns a signed KILL directive.
     */
    public function killLicense(License $license, ?string $reason = null, bool $blacklist = false): License
    {
        return DB::transaction(function () use ($license, $reason, $blacklist): License {
            $this->revokeActivations($license, 'kill', $reason ?? 'Remote kill switch engaged');

            $license->forceFill([
                'status'           => LicenseStatus::Killed->value,
                'kill_switch'      => true,
                'killed_at'        => now(),
                'activation_count' => 0,
            ])->save();

            if ($blacklist) {
                $this->blacklist->add([
                    'license_id' => $license->id,
                    'reason'     => $reason ?? 'Killed and blacklisted by operator',
                ], killLicense: false);
            }

            AuditLogger::record(
                AuditEvent::LicenseKilled,
                $license,
                $reason ?? 'Remote kill switch engaged',
                meta: ['blacklisted' => $blacklist],
            );

            return $license->refresh();
        });
    }

    /**
     * Reverse a kill: clear the switch and re-enable activation.
     * (Activations are NOT restored — clients must re-activate.)
     */
    public function reviveLicense(License $license): License
    {
        return DB::transaction(function () use ($license): License {
            $license->forceFill([
                'status'      => LicenseStatus::Active->value,
                'kill_switch' => false,
                'killed_at'   => null,
            ])->save();

            AuditLogger::record(
                AuditEvent::LicenseActivated,
                $license,
                'Kill switch released; license re-enabled',
            );

            return $license->refresh();
        });
    }

    /* ================================================================
     |  Suspend / Resume
     * ================================================================ */

    /**
     * Suspend a license without revoking activations. The next verify returns
     * a DENY; resuming restores service without forcing re-activation.
     */
    public function suspendLicense(License $license, ?string $reason = null): License
    {
        return DB::transaction(function () use ($license, $reason): License {
            $license->forceFill(['status' => LicenseStatus::Suspended->value])->save();

            $this->logActivationAction($license, null, 'suspend', $reason ?? 'License suspended');

            AuditLogger::record(
                AuditEvent::LicenseSuspended,
                $license,
                $reason ?? 'License suspended',
            );

            return $license->refresh();
        });
    }

    public function resumeLicense(License $license): License
    {
        return DB::transaction(function () use ($license): License {
            // Only resume if not killed/expired — otherwise leave as-is.
            if (in_array($license->status, [LicenseStatus::Killed, LicenseStatus::Expired], true)) {
                return $license;
            }

            $license->forceFill(['status' => LicenseStatus::Active->value])->save();

            AuditLogger::record(
                AuditEvent::LicenseActivated,
                $license,
                'License resumed from suspension',
            );

            return $license->refresh();
        });
    }

    /* ================================================================
     |  Disable Customer (cascade)
     * ================================================================ */

    /**
     * Disable a customer and kill ALL of their licenses. Returns the number of
     * licenses affected.
     */
    public function disableCustomer(Customer $customer, ?string $reason = null): int
    {
        return DB::transaction(function () use ($customer, $reason): int {
            $customer->forceFill(['is_active' => false])->save();

            $affected = 0;
            $customer->licenses()
                ->whereNotIn('status', [LicenseStatus::Killed->value])
                ->get()
                ->each(function (License $license) use ($reason, &$affected): void {
                    $this->killLicense($license, $reason ?? 'Customer account disabled');
                    $affected++;
                });

            AuditLogger::record(
                AuditEvent::LicenseKilled,
                $customer,
                "Customer disabled; {$affected} license(s) killed",
                meta: ['reason' => $reason, 'licenses_affected' => $affected],
            );

            return $affected;
        });
    }

    /**
     * Re-enable a customer. Their licenses are NOT auto-revived — the operator
     * revives individually to stay deliberate about what comes back online.
     */
    public function enableCustomer(Customer $customer): Customer
    {
        $customer->forceFill(['is_active' => true])->save();

        AuditLogger::record(
            AuditEvent::LicenseActivated,
            $customer,
            'Customer account re-enabled',
        );

        return $customer->refresh();
    }

    /* ================================================================
     |  Disable Domain
     * ================================================================ */

    /**
     * Disable a specific domain for a license: revoke every active activation
     * bound to it and add a blacklist entry so it cannot re-activate.
     *
     * @return int number of activations revoked
     */
    public function disableDomain(License $license, string $domain, ?string $reason = null): int
    {
        return DB::transaction(function () use ($license, $domain, $reason): int {
            $normalized = DomainNormalizer::normalize($domain);

            $activations = $license->activations()
                ->where('status', ActivationStatus::Active->value)
                ->where('normalized_domain', $normalized)
                ->get();

            foreach ($activations as $activation) {
                $this->revokeActivation($activation, 'deactivate', $reason ?? 'Domain disabled');
            }

            $license->forceFill([
                'activation_count' => $license->activations()->active()->count(),
            ])->save();

            $this->blacklist->add([
                'license_id' => $license->id,
                'domain'     => $normalized,
                'reason'     => $reason ?? "Domain disabled: {$normalized}",
            ], killLicense: false);

            AuditLogger::record(
                AuditEvent::Blacklisted,
                $license,
                "Domain disabled: {$normalized}",
                meta: ['activations_revoked' => $activations->count()],
            );

            return $activations->count();
        });
    }

    /* ================================================================
     |  Disable Installation
     * ================================================================ */

    /**
     * Disable a single installation: revoke its binding and blacklist the
     * installation_id so it cannot re-activate on the same license.
     */
    public function disableInstallation(License $license, string $installationId, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($license, $installationId, $reason): bool {
            $activation = $license->activations()
                ->where('installation_id', $installationId)
                ->first();

            if ($activation === null) {
                return false;
            }

            if ($activation->status === ActivationStatus::Active) {
                $this->revokeActivation($activation, 'deactivate', $reason ?? 'Installation disabled');
                $license->forceFill([
                    'activation_count' => $license->activations()->active()->count(),
                ])->save();
            }

            $this->blacklist->add([
                'license_id'      => $license->id,
                'installation_id' => $installationId,
                'reason'          => $reason ?? "Installation disabled: {$installationId}",
            ], killLicense: false);

            AuditLogger::record(
                AuditEvent::ActivationRevoked,
                $license,
                "Installation disabled: {$installationId}",
                meta: ['reason' => $reason],
            );

            return true;
        });
    }

    /* ================================================================
     |  Internal helpers
     * ================================================================ */

    private function revokeActivations(License $license, string $action, string $reason): void
    {
        $license->activations()
            ->where('status', ActivationStatus::Active->value)
            ->get()
            ->each(fn (LicenseActivation $a) => $this->revokeActivation($a, $action, $reason));
    }

    private function revokeActivation(LicenseActivation $activation, string $action, string $reason): void
    {
        $activation->forceFill([
            'status'     => ActivationStatus::Revoked->value,
            'revoked_at' => now(),
        ])->save();

        $this->logActivationAction($activation->license, $activation, $action, $reason);
    }

    private function logActivationAction(
        License $license,
        ?LicenseActivation $activation,
        string $action,
        string $reason,
    ): void {
        ActivationLog::create([
            'license_id'            => $license->id,
            'license_activation_id' => $activation?->id,
            'action'                => $action,
            'success'               => true,
            'reason'                => $reason,
            'installation_id'       => $activation?->installation_id,
            'normalized_domain'     => $activation?->normalized_domain,
            'server_type'           => $activation?->server_type,
            'ip_address'            => $activation?->ip_address,
        ]);
    }
}
