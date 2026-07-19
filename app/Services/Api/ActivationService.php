<?php

declare(strict_types=1);

namespace App\Services\Api;

use App\Enums\ActivationStatus;
use App\Enums\AuditEvent;
use App\Enums\LicenseType;
use App\Models\ActivationLog;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Support\AuditLogger;
use App\Support\DomainNormalizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Handles the activation lifecycle for ERP clients: first-time binds,
 * idempotent re-activations, and the signed token returned to the client.
 */
class ActivationService
{
    public function __construct(
        private readonly LicenseValidationService $validator,
        private readonly RsaSignatureService $signer,
    ) {
    }

    /**
     * Activate (or idempotently re-activate) an installation against a license.
     *
     * @param array{
     *   installation_id:string, domain:?string, server_type:?string,
     *   fingerprint:?string, os_info:?string, ip:?string, user_agent:?string
     * } $context
     *
     * @return array{license:License, activation:LicenseActivation, signature:string, payload:array}
     */
    public function activate(License $license, array $context): array
    {
        return DB::transaction(function () use ($license, $context): array {
            $installationId = $context['installation_id'];
            $normalizedDomain = DomainNormalizer::normalize($context['domain'] ?? null);
            $ip = $context['ip'] ?? null;

            // Run the full guard chain (order matters: cheap → expensive).
            $this->validator->assertUsable($license);
            $this->validator->assertNotBlacklisted($license, $installationId, $normalizedDomain, $ip);
            $this->validator->assertServerTypeAllowed($license, $context['server_type'] ?? null);
            $this->validator->assertDomainAllowed($license, $context['domain'] ?? null);
            $this->validator->assertActivationSlotAvailable($license, $installationId);

            // Idempotent: reuse an existing active binding for this installation.
            $activation = $license->activations()
                ->where('installation_id', $installationId)
                ->first();

            $isNew = $activation === null || $activation->status !== ActivationStatus::Active;

            if ($activation === null) {
                $activation = new LicenseActivation();
                $activation->license_id = $license->id;
                $activation->installation_id = $installationId;
            }

            $activation->fill([
                'uuid'              => $activation->uuid ?? (string) Str::uuid(),
                'fingerprint_hash'  => $context['fingerprint'] ?? null,
                'domain'            => $context['domain'] ?? null,
                'normalized_domain' => $normalizedDomain,
                'is_wildcard'       => DomainNormalizer::isWildcard($context['domain'] ?? null),
                'server_type'       => $context['server_type'] ?? null,
                'ip_address'        => $ip,
                'os_info'           => $context['os_info'] ?? null,
                'user_agent'        => $context['user_agent'] ?? null,
                'status'            => ActivationStatus::Active->value,
                'activated_at'      => $activation->activated_at ?? now(),
                'last_verified_at'  => now(),
                'revoked_at'        => null,
            ])->save();

            // Recompute the denormalized counter from source of truth.
            $activeCount = $license->activations()->active()->count();
            $license->forceFill(['activation_count' => $activeCount])->save();

            // Audit + activation log.
            $this->logActivation($license, $activation, $isNew ? 'activate' : 'reactivate', true, $context);

            AuditLogger::record(
                AuditEvent::LicenseActivated,
                $license,
                "Installation {$installationId} activated",
                newValues: ['installation_id' => $installationId, 'domain' => $normalizedDomain],
                actorType: 'api_client',
            );

            // Build + sign the response payload the client will store & re-check.
            $payload = $this->buildSignedPayload($license, $activation);
            $signature = $this->signer->sign($payload);

            return [
                'license'    => $license,
                'activation' => $activation,
                'signature'  => $signature,
                'payload'    => $payload,
            ];
        });
    }

    /**
     * The canonical, signable payload describing the activation grant.
     */
    public function buildSignedPayload(License $license, LicenseActivation $activation): array
    {
        return [
            'license_uuid'     => $license->uuid,
            'installation_id'  => $activation->installation_id,
            'domain'           => $activation->normalized_domain,
            'type'             => $license->type->value,
            'product'          => $license->product,
            'max_activations'  => $license->max_activations,
            'expires_at'       => $license->expires_at?->toIso8601String(),
            'grace_days'       => $license->grace_days,
            'verify_interval'  => $license->verification_interval_hours,
            'key_version'      => $license->rsa_key_version ?? $this->signer->keyVersion(),
            'issued_at'        => now()->toIso8601String(),
        ];
    }

    private function logActivation(
        License $license,
        LicenseActivation $activation,
        string $action,
        bool $success,
        array $context,
        ?string $reason = null,
    ): void {
        ActivationLog::create([
            'license_id'            => $license->id,
            'license_activation_id' => $activation->id,
            'action'                => $action,
            'success'               => $success,
            'reason'                => $reason,
            'installation_id'       => $activation->installation_id,
            'normalized_domain'     => $activation->normalized_domain,
            'server_type'           => $activation->server_type,
            'ip_address'            => $activation->ip_address,
            'user_agent'            => $activation->user_agent,
            'request_payload'       => ['server_type' => $context['server_type'] ?? null],
        ]);
    }
}
