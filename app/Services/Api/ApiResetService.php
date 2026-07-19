<?php

declare(strict_types=1);

namespace App\Services\Api;

use App\Enums\ActivationStatus;
use App\Enums\ApiErrorCode;
use App\Enums\AuditEvent;
use App\Exceptions\Api\LicenseApiException;
use App\Models\ActivationLog;
use App\Models\License;
use App\Models\LicenseReset;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

/**
 * Client-initiated reset: releases a specific installation (or all of them)
 * so the license can be re-activated on new hardware/domain. This is the
 * self-service counterpart to the admin reset in Phase 3.
 */
class ApiResetService
{
    /**
     * @param array{installation_id:?string, reason:?string, ip:?string} $context
     * @return array{cleared:int, reset:LicenseReset}
     */
    public function reset(License $license, array $context): array
    {
        return DB::transaction(function () use ($license, $context): array {
            $installationId = $context['installation_id'] ?? null;

            $query = $license->activations()->where('status', ActivationStatus::Active->value);

            if ($installationId !== null) {
                $query->where('installation_id', $installationId);

                // A targeted reset must reference a real, active binding.
                if (! (clone $query)->exists()) {
                    throw LicenseApiException::make(
                        ApiErrorCode::InstallationNotFound,
                        'No active installation matches the supplied identifier.',
                    );
                }
            }

            $activations = $query->get();
            $cleared = $activations->count();

            foreach ($activations as $activation) {
                $activation->update([
                    'status'     => ActivationStatus::Revoked->value,
                    'revoked_at' => now(),
                ]);

                ActivationLog::create([
                    'license_id'            => $license->id,
                    'license_activation_id' => $activation->id,
                    'action'                => 'deactivate',
                    'success'               => true,
                    'reason'                => $context['reason'] ?? 'Client-initiated reset',
                    'installation_id'       => $activation->installation_id,
                    'normalized_domain'     => $activation->normalized_domain,
                    'ip_address'            => $context['ip'] ?? null,
                ]);
            }

            $activeCount = $license->activations()->active()->count();
            $license->forceFill(['activation_count' => $activeCount])->save();

            $reset = LicenseReset::create([
                'license_id'          => $license->id,
                'reason'              => $context['reason'] ?? 'Client-initiated reset',
                'activations_cleared' => $cleared,
                'old_rsa_key_version' => $license->rsa_key_version,
                'new_rsa_key_version' => $license->rsa_key_version, // API reset keeps key version
                'performed_by'        => null,
                'performed_by_name'   => 'API Client',
                'ip_address'          => $context['ip'] ?? null,
                'reset_at'            => now(),
            ]);

            AuditLogger::record(
                AuditEvent::LicenseReset,
                $license,
                "Client reset cleared {$cleared} activation(s)",
                meta: ['installation_id' => $installationId, 'scope' => $installationId ? 'single' : 'all'],
                actorType: 'api_client',
            );

            return ['cleared' => $cleared, 'reset' => $reset];
        });
    }
}
