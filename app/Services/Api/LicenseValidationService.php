<?php

declare(strict_types=1);

namespace App\Services\Api;

use App\Enums\ActivationStatus;
use App\Enums\ApiErrorCode;
use App\Enums\LicenseStatus;
use App\Enums\LicenseType;
use App\Exceptions\Api\LicenseApiException;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\LicenseBlacklist;
use App\Repositories\Contracts\LicenseRepositoryInterface;
use App\Support\DomainNormalizer;

/**
 * Shared, stateless validation used by every client endpoint. Each guard
 * throws LicenseApiException on failure so callers can compose them freely.
 */
class LicenseValidationService
{
    public function __construct(
        private readonly LicenseRepositoryInterface $licenses,
    ) {
    }

    /**
     * Resolve a license from its plaintext key (hashed for lookup) or throw.
     */
    public function resolveByKey(string $plainKey): License
    {
        $hash = hash('sha256', $plainKey);
        $license = $this->licenses->findByKeyHash($hash);

        if ($license === null) {
            throw LicenseApiException::make(
                ApiErrorCode::LicenseNotFound,
                'No license matches the supplied key.',
            );
        }

        return $license;
    }

    /**
     * Ensure the license is in a usable state: active, not killed, not expired.
     */
    public function assertUsable(License $license): void
    {
        if ($license->kill_switch || $license->status === LicenseStatus::Killed) {
            throw LicenseApiException::make(
                ApiErrorCode::LicenseKilled,
                'This license has been terminated by the vendor.',
            );
        }

        if ($license->status === LicenseStatus::Suspended) {
            throw LicenseApiException::make(
                ApiErrorCode::LicenseSuspended,
                'This license is currently suspended.',
            );
        }

        if ($license->status !== LicenseStatus::Active) {
            throw LicenseApiException::make(
                ApiErrorCode::LicenseInactive,
                'This license is not active.',
                ['status' => $license->status->value],
            );
        }

        if ($license->isExpired()) {
            throw LicenseApiException::make(
                ApiErrorCode::LicenseExpired,
                'This license has expired.',
                ['expired_at' => $license->expires_at?->toIso8601String()],
            );
        }
    }

    /**
     * Domain lock: the requesting domain must match the license type policy.
     * Localhost licenses accept localhost/127.0.0.1 only; domain/vps licenses
     * match against their bound activations (or accept first-bind).
     */
    public function assertDomainAllowed(License $license, ?string $rawDomain): ?string
    {
        $normalized = DomainNormalizer::normalize($rawDomain);

        if ($license->type === LicenseType::Localhost) {
            if (! in_array($normalized, ['localhost', '127.0.0.1', null], true)) {
                throw LicenseApiException::make(
                    ApiErrorCode::DomainMismatch,
                    'Localhost licenses cannot be used on a public domain.',
                    ['domain' => $normalized],
                );
            }

            return $normalized;
        }

        // Domain/VPS: if activations already bind a domain, enforce it.
        $bound = $license->activations()
            ->active()
            ->whereNotNull('normalized_domain')
            ->pluck('normalized_domain')
            ->all();

        if ($normalized === null) {
            throw LicenseApiException::make(
                ApiErrorCode::DomainMismatch,
                'A domain is required for this license type.',
            );
        }

        if ($bound !== [] && ! $this->domainMatchesAny($normalized, $bound)) {
            throw LicenseApiException::make(
                ApiErrorCode::DomainLocked,
                'This license is locked to a different domain.',
                ['domain' => $normalized, 'locked_to' => $bound],
            );
        }

        return $normalized;
    }

    /**
     * Installation lock: verify a supplied installation_id belongs to this
     * license and is still active. Used by check-installation / verify.
     */
    public function assertInstallationBound(License $license, string $installationId): LicenseActivation
    {
        $activation = $license->activations()
            ->where('installation_id', $installationId)
            ->first();

        if ($activation === null) {
            throw LicenseApiException::make(
                ApiErrorCode::InstallationNotFound,
                'This installation is not registered for the license.',
            );
        }

        if ($activation->status !== ActivationStatus::Active) {
            throw LicenseApiException::make(
                ApiErrorCode::InstallationMismatch,
                'This installation has been revoked.',
            );
        }

        return $activation;
    }

    /**
     * Blacklist guard: block if the license, installation, domain, or IP has an
     * active blacklist entry.
     */
    public function assertNotBlacklisted(
        License $license,
        ?string $installationId,
        ?string $normalizedDomain,
        ?string $ip,
    ): void {
        $blocked = LicenseBlacklist::query()
            ->active()
            ->where(function ($q) use ($license, $installationId, $normalizedDomain, $ip): void {
                $q->where('license_id', $license->id)
                    ->orWhere('license_key_hash', $license->license_key_hash);

                if ($installationId !== null) {
                    $q->orWhere('installation_id', $installationId);
                }
                if ($normalizedDomain !== null) {
                    $q->orWhere('normalized_domain', $normalizedDomain);
                }
                if ($ip !== null) {
                    $q->orWhere('ip_address', $ip);
                }
            })
            ->exists();

        if ($blocked) {
            throw LicenseApiException::make(
                ApiErrorCode::Blacklisted,
                'Access denied. This license or environment has been blocked.',
            );
        }
    }

    /**
     * Activation-limit guard for new binds. Existing installations are exempt
     * (they re-verify rather than consume a new slot).
     */
    public function assertActivationSlotAvailable(License $license, string $installationId): void
    {
        $alreadyBound = $license->activations()
            ->where('installation_id', $installationId)
            ->active()
            ->exists();

        if ($alreadyBound) {
            return;
        }

        $activeCount = $license->activations()->active()->count();

        if ($activeCount >= $license->max_activations) {
            throw LicenseApiException::make(
                ApiErrorCode::ActivationLimit,
                'The maximum number of activations for this license has been reached.',
                [
                    'max_activations'    => $license->max_activations,
                    'active_activations' => $activeCount,
                ],
            );
        }
    }

    /**
     * Server-type policy: the declared environment must match the license type.
     * Localhost licenses only permit localhost; domain licenses reject localhost;
     * VPS licenses accept domain or vps. A missing server_type is permissive.
     */
    public function assertServerTypeAllowed(License $license, ?string $serverType): void
    {
        if ($serverType === null || $serverType === '') {
            return;
        }

        $serverType = strtolower($serverType);

        $allowed = match ($license->type) {
            LicenseType::Localhost => ['localhost'],
            LicenseType::Domain    => ['domain', 'vps'],
            LicenseType::Vps       => ['vps', 'domain'],
        };

        if (! in_array($serverType, $allowed, true)) {
            throw LicenseApiException::make(
                ApiErrorCode::ServerTypeNotAllowed,
                'The server environment is not permitted for this license type.',
                ['server_type' => $serverType, 'license_type' => $license->type->value],
            );
        }
    }

    private function domainMatchesAny(string $domain, array $candidates): bool
    {
        foreach ($candidates as $candidate) {
            if ($candidate === $domain) {
                return true;
            }
            // Wildcard subdomain support: *.example.com matches app.example.com
            if (str_starts_with($candidate, '*.')) {
                $base = substr($candidate, 2);
                if ($domain === $base || str_ends_with($domain, '.' . $base)) {
                    return true;
                }
            }
        }

        return false;
    }
}
