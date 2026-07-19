<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ActivationStatus;
use App\Enums\AuditEvent;
use App\Enums\LicenseStatus;
use App\Models\License;
use App\Models\LicenseReset;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class LicenseResetService
{
    /**
     * Reset a license: revoke all activations, reset the counter, rotate the
     * RSA key version, and record an auditable reset entry.
     */
    public function reset(License $license, ?string $reason = null): LicenseReset
    {
        return DB::transaction(function () use ($license, $reason): LicenseReset {
            $cleared = $license->activations()
                ->where('status', ActivationStatus::Active->value)
                ->count();

            $license->activations()
                ->where('status', ActivationStatus::Active->value)
                ->update([
                    'status'     => ActivationStatus::Revoked->value,
                    'revoked_at' => now(),
                ]);

            $oldVersion = $license->rsa_key_version;
            $newVersion = $this->nextKeyVersion($oldVersion);

            $license->fill([
                'status'           => LicenseStatus::Active->value,
                'kill_switch'      => false,
                'killed_at'        => null,
                'activation_count' => 0,
                'rsa_key_version'  => $newVersion,
                'last_verified_at' => null,
            ])->save();

            $user = Auth::user();

            $reset = LicenseReset::create([
                'license_id'          => $license->id,
                'reason'              => $reason,
                'activations_cleared' => $cleared,
                'old_rsa_key_version' => $oldVersion,
                'new_rsa_key_version' => $newVersion,
                'performed_by'        => $user?->getAuthIdentifier(),
                'performed_by_name'   => $user->name ?? 'System',
                'ip_address'          => Request::ip(),
                'reset_at'            => now(),
            ]);

            AuditLogger::record(
                AuditEvent::LicenseReset,
                $license,
                "License reset — {$cleared} activation(s) cleared",
                meta: ['reason' => $reason, 'reset_id' => $reset->id],
            );

            return $reset;
        });
    }

    private function nextKeyVersion(?string $current): string
    {
        $number = (int) preg_replace('/\D/', '', (string) $current);

        return 'v' . ($number + 1 ?: 2);
    }
}
