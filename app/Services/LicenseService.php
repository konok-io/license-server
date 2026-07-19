<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ActivationStatus;
use App\Enums\AuditEvent;
use App\Enums\LicenseStatus;
use App\Models\License;
use App\Repositories\Contracts\LicenseRepositoryInterface;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

class LicenseService
{
    public function __construct(
        private readonly LicenseRepositoryInterface $licenses,
        private readonly LicenseKeyService $keys,
    ) {
    }

    /**
     * Issue a new license. Returns the model plus the one-time plaintext key.
     *
     * @return array{license:License, plain_key:string}
     */
    public function issue(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $key = $this->keys->generate();

            $license = $this->licenses->create([
                ...$data,
                'license_key_encrypted' => $key['plain'],
                'license_key_hash'      => $key['hash'],
                'license_key_prefix'    => $key['prefix'],
                'status'                => $data['status'] ?? LicenseStatus::Active->value,
                'issued_at'             => now(),
                'rsa_key_version'       => $data['rsa_key_version'] ?? 'v1',
            ]);

            AuditLogger::record(
                AuditEvent::LicenseIssued,
                $license,
                "License {$license->license_key_prefix}… issued",
                newValues: ['type' => $license->type->value, 'status' => $license->status->value],
            );

            return ['license' => $license, 'plain_key' => $key['plain']];
        });
    }

    public function update(License $license, array $data): License
    {
        return DB::transaction(function () use ($license, $data): License {
            $old = $license->only(['status', 'type', 'max_activations', 'expires_at']);
            $updated = $this->licenses->update($license, $data);

            AuditLogger::record(
                AuditEvent::LicenseIssued,
                $updated,
                "License {$updated->license_key_prefix}… updated",
                oldValues: $old,
                newValues: $updated->only(['status', 'type', 'max_activations', 'expires_at']),
            );

            return $updated;
        });
    }

    public function suspend(License $license): License
    {
        $updated = $this->licenses->update($license, [
            'status' => LicenseStatus::Suspended->value,
        ]);

        AuditLogger::record(AuditEvent::LicenseSuspended, $updated, 'License suspended');

        return $updated;
    }

    public function reactivate(License $license): License
    {
        $updated = $this->licenses->update($license, [
            'status'      => LicenseStatus::Active->value,
            'kill_switch' => false,
            'killed_at'   => null,
        ]);

        AuditLogger::record(AuditEvent::LicenseActivated, $updated, 'License reactivated');

        return $updated;
    }

    /**
     * Remote kill switch: flags the license so the next client verification
     * receives a KILL directive. All active activations are revoked.
     */
    public function kill(License $license, ?string $reason = null): License
    {
        return DB::transaction(function () use ($license, $reason): License {
            $license->activations()
                ->where('status', ActivationStatus::Active->value)
                ->update([
                    'status'     => ActivationStatus::Revoked->value,
                    'revoked_at' => now(),
                ]);

            $updated = $this->licenses->update($license, [
                'status'           => LicenseStatus::Killed->value,
                'kill_switch'      => true,
                'killed_at'        => now(),
                'activation_count' => 0,
            ]);

            AuditLogger::record(
                AuditEvent::LicenseKilled,
                $updated,
                $reason ?? 'Remote kill switch engaged',
                meta: ['reason' => $reason],
            );

            return $updated;
        });
    }

    public function delete(License $license): bool
    {
        return DB::transaction(fn (): bool => $this->licenses->delete($license));
    }

    /** @return array<string, int> */
    public function dashboardCounts(): array
    {
        return $this->licenses->statusCounts();
    }
}
