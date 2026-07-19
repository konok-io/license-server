<?php

declare(strict_types=1);

namespace App\Services\Api;

use App\Enums\ActivationStatus;
use App\Enums\AuditEvent;
use App\Enums\LicenseStatus;
use App\Enums\VerificationAction;
use App\Enums\VerificationResult;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\LicenseBlacklist;
use App\Models\LicenseVerification;
use App\Models\VerificationLog;
use App\Support\AuditLogger;
use App\Support\DomainNormalizer;
use Illuminate\Support\Facades\DB;

/**
 * Daily verification protocol.
 *
 * A client sends its license key + installation_id (+ optional domain/nonce)
 * on a schedule. The server evaluates the license/installation state, decides
 * an action (continue / kill / grace / expire / reactivate / deny), records the
 * verification + log, and returns an RSA-4096 SIGNED payload. Because the
 * verdict is signed, the client can trust it and cannot be spoofed by a MITM.
 */
class VerificationService
{
    public function __construct(
        private readonly RsaSignatureService $signer,
    ) {
    }

    /**
     * @param array{
     *   installation_id:string, domain:?string, nonce:?string,
     *   ip:?string, user_agent:?string, request:?array
     * } $context
     *
     * @return array{action:VerificationAction, result:VerificationResult, payload:array, signature:string, http_status:int}
     */
    public function verify(License $license, array $context): array
    {
        $startedAt = microtime(true);

        return DB::transaction(function () use ($license, $context, $startedAt): array {
            $installationId   = $context['installation_id'];
            $normalizedDomain = DomainNormalizer::normalize($context['domain'] ?? null);
            $ip               = $context['ip'] ?? null;

            // Resolve the activation binding (may be null / revoked).
            $activation = $license->activations()
                ->where('installation_id', $installationId)
                ->first();

            // Decide the verdict.
            [$action, $result, $httpStatus] = $this->decide(
                $license,
                $activation,
                $installationId,
                $normalizedDomain,
                $ip,
            );

            // Side effects based on the verdict.
            $this->applySideEffects($license, $activation, $action);

            $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);

            // Persist the immutable verification record.
            $verification = LicenseVerification::create([
                'license_id'            => $license->id,
                'license_activation_id' => $activation?->id,
                'result'                => $result->value,
                'installation_id'       => $installationId,
                'normalized_domain'     => $normalizedDomain,
                'ip_address'            => $ip,
                'nonce'                 => $context['nonce'] ?? null,
                'payload_hash'          => null, // set below once payload built
                'latency_ms'            => $latencyMs,
                'verified_at'           => now(),
            ]);

            // Build + sign the response payload.
            $payload   = $this->buildPayload($license, $activation, $action, $result);
            $signature = $this->signer->sign($payload);

            // Backfill the payload hash for tamper-evidence in the record.
            $verification->forceFill([
                'payload_hash' => hash('sha256', $this->signer->canonicalize($payload)),
            ])->save();

            // Detailed transport log (request + response snapshot).
            VerificationLog::create([
                'license_id'              => $license->id,
                'license_verification_id' => $verification->id,
                'result'                  => $result->value,
                'kill_directive'          => $action === VerificationAction::Kill,
                'installation_id'         => $installationId,
                'normalized_domain'       => $normalizedDomain,
                'ip_address'              => $ip,
                'user_agent'              => $context['user_agent'] ?? null,
                'nonce'                   => $context['nonce'] ?? null,
                'latency_ms'              => $latencyMs,
                'request_payload'         => $context['request'] ?? null,
                'response_payload'        => ['action' => $action->value, 'result' => $result->value],
            ]);

            // Audit trail (immutable, hash-chained).
            AuditLogger::record(
                AuditEvent::LicenseVerified,
                $license,
                "Verification for {$installationId}: {$action->label()}",
                newValues: ['action' => $action->value, 'result' => $result->value],
                actorType: 'api_client',
            );

            return [
                'action'      => $action,
                'result'      => $result,
                'payload'     => $payload,
                'signature'   => $signature,
                'http_status' => $httpStatus,
            ];
        });
    }

    /**
     * Pure decision function — no side effects.
     *
     * @return array{0:VerificationAction, 1:VerificationResult, 2:int}
     */
    private function decide(
        License $license,
        ?LicenseActivation $activation,
        string $installationId,
        ?string $normalizedDomain,
        ?string $ip,
    ): array {
        // 1. Blacklist takes precedence over everything.
        if ($this->isBlacklisted($license, $installationId, $normalizedDomain, $ip)) {
            return [VerificationAction::Deny, VerificationResult::Blacklisted, 403];
        }

        // 2. Remote kill switch.
        if ($license->kill_switch || $license->status === LicenseStatus::Killed) {
            return [VerificationAction::Kill, VerificationResult::Killed, 403];
        }

        // 3. Suspended.
        if ($license->status === LicenseStatus::Suspended) {
            return [VerificationAction::Deny, VerificationResult::Failed, 403];
        }

        // 4. Installation binding must exist and be active.
        if ($activation === null || $activation->status !== ActivationStatus::Active) {
            return [VerificationAction::Reactivate, VerificationResult::InstallMismatch, 403];
        }

        // 5. Domain lock (if the client reports a domain).
        if ($normalizedDomain !== null
            && $activation->normalized_domain !== null
            && ! $this->domainMatches($normalizedDomain, $activation)
        ) {
            return [VerificationAction::Deny, VerificationResult::DomainMismatch, 403];
        }

        // 6. Expiry + grace-period handling.
        if ($license->expires_at !== null && $license->expires_at->isPast()) {
            $graceEnds = $license->expires_at->copy()->addDays($license->grace_days);

            if (now()->lessThanOrEqualTo($graceEnds)) {
                return [VerificationAction::Grace, VerificationResult::Success, 200];
            }

            return [VerificationAction::Expire, VerificationResult::Expired, 403];
        }

        // 7. All good.
        return [VerificationAction::Continue, VerificationResult::Success, 200];
    }

    /**
     * Mutations driven by the verdict: refresh heartbeat, revoke bindings,
     * flip license status when grace elapses.
     */
    private function applySideEffects(
        License $license,
        ?LicenseActivation $activation,
        VerificationAction $action,
    ): void {
        switch ($action) {
            case VerificationAction::Continue:
            case VerificationAction::Grace:
                $license->forceFill(['last_verified_at' => now()])->save();
                $activation?->forceFill(['last_verified_at' => now()])->save();
                break;

            case VerificationAction::Expire:
                if ($license->status !== LicenseStatus::Expired) {
                    $license->forceFill(['status' => LicenseStatus::Expired->value])->save();
                }
                break;

            case VerificationAction::Reactivate:
                // Nothing to persist; the client is told to re-activate.
                break;

            case VerificationAction::Kill:
            case VerificationAction::Deny:
                // Ensure any active binding is revoked on a kill.
                if ($action === VerificationAction::Kill
                    && $activation !== null
                    && $activation->status === ActivationStatus::Active
                ) {
                    $activation->forceFill([
                        'status'     => ActivationStatus::Revoked->value,
                        'revoked_at' => now(),
                    ])->save();

                    $license->forceFill([
                        'activation_count' => $license->activations()->active()->count(),
                    ])->save();
                }
                break;
        }
    }

    /**
     * The signed verdict the client stores and trusts. Includes a fresh
     * validity window so the client knows when it must next verify.
     */
    private function buildPayload(
        License $license,
        ?LicenseActivation $activation,
        VerificationAction $action,
        VerificationResult $result,
    ): array {
        $nextVerifyBy = now()->addHours($license->verification_interval_hours);

        return [
            'license_uuid'     => $license->uuid,
            'installation_id'  => $activation?->installation_id,
            'action'           => $action->value,
            'result'           => $result->value,
            'operational'      => $action->isOperational(),
            'status'           => $license->status->value,
            'expires_at'       => $license->expires_at?->toIso8601String(),
            'grace_days'       => $license->grace_days,
            'verify_interval'  => $license->verification_interval_hours,
            'next_verify_by'   => $nextVerifyBy->toIso8601String(),
            'key_version'      => $license->rsa_key_version ?? $this->signer->keyVersion(),
            'verified_at'      => now()->toIso8601String(),
        ];
    }

    private function isBlacklisted(
        License $license,
        string $installationId,
        ?string $normalizedDomain,
        ?string $ip,
    ): bool {
        return LicenseBlacklist::query()
            ->active()
            ->where(function ($q) use ($license, $installationId, $normalizedDomain, $ip): void {
                $q->where('license_id', $license->id)
                    ->orWhere('license_key_hash', $license->license_key_hash)
                    ->orWhere('installation_id', $installationId);

                if ($normalizedDomain !== null) {
                    $q->orWhere('normalized_domain', $normalizedDomain);
                }
                if ($ip !== null) {
                    $q->orWhere('ip_address', $ip);
                }
            })
            ->exists();
    }

    private function domainMatches(string $domain, LicenseActivation $activation): bool
    {
        $bound = $activation->normalized_domain;

        if ($bound === $domain) {
            return true;
        }

        // Wildcard subdomain support (*.example.com).
        if ($activation->is_wildcard && str_starts_with((string) $bound, '*.')) {
            $base = substr((string) $bound, 2);

            return $domain === $base || str_ends_with($domain, '.' . $base);
        }

        return false;
    }
}
