<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AuditEvent;
use App\Enums\LicenseStatus;
use App\Models\License;
use App\Models\LicenseBlacklist;
use App\Support\AuditLogger;
use App\Support\DomainNormalizer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BlacklistService
{
    /**
     * Add a blacklist entry. Optionally kills the linked license so the block
     * takes effect on the next verification cycle.
     */
    public function add(array $data, bool $killLicense = true): LicenseBlacklist
    {
        return DB::transaction(function () use ($data, $killLicense): LicenseBlacklist {
            $user = Auth::user();

            $license = isset($data['license_id'])
                ? License::find($data['license_id'])
                : null;

            $entry = LicenseBlacklist::create([
                'license_id'        => $data['license_id'] ?? null,
                'installation_id'   => $data['installation_id'] ?? null,
                'normalized_domain' => DomainNormalizer::normalize($data['domain'] ?? null),
                'ip_address'        => $data['ip_address'] ?? null,
                'license_key_hash'  => $license?->license_key_hash,
                'reason'            => $data['reason'],
                'is_active'         => true,
                'created_by'        => $user?->getAuthIdentifier(),
                'created_by_name'   => $user->name ?? 'System',
                'blacklisted_at'    => now(),
            ]);

            if ($killLicense && $license !== null) {
                $license->update([
                    'status'      => LicenseStatus::Killed->value,
                    'kill_switch' => true,
                    'killed_at'   => now(),
                ]);
            }

            AuditLogger::record(
                AuditEvent::Blacklisted,
                $entry,
                "Blacklist entry created: {$entry->reason}",
                newValues: $entry->only(['installation_id', 'normalized_domain', 'ip_address']),
            );

            return $entry;
        });
    }

    public function lift(LicenseBlacklist $entry): LicenseBlacklist
    {
        $entry->update([
            'is_active' => false,
            'lifted_at' => now(),
        ]);

        AuditLogger::record(
            AuditEvent::Blacklisted,
            $entry,
            'Blacklist entry lifted',
        );

        return $entry->refresh();
    }

    public function delete(LicenseBlacklist $entry): bool
    {
        return (bool) $entry->delete();
    }
}
