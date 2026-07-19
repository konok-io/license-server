<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Stable machine-readable error codes returned to ERP clients.
 * Clients branch on `error.code`, never on human-readable messages.
 */
enum ApiErrorCode: string
{
    case ValidationFailed      = 'VALIDATION_FAILED';
    case LicenseNotFound       = 'LICENSE_NOT_FOUND';
    case LicenseInactive       = 'LICENSE_INACTIVE';
    case LicenseExpired        = 'LICENSE_EXPIRED';
    case LicenseKilled         = 'LICENSE_KILLED';
    case LicenseSuspended      = 'LICENSE_SUSPENDED';
    case ActivationLimit       = 'ACTIVATION_LIMIT_REACHED';
    case DomainMismatch        = 'DOMAIN_MISMATCH';
    case DomainLocked          = 'DOMAIN_LOCKED';
    case InstallationMismatch  = 'INSTALLATION_MISMATCH';
    case InstallationNotFound  = 'INSTALLATION_NOT_FOUND';
    case Blacklisted           = 'BLACKLISTED';
    case ServerTypeNotAllowed  = 'SERVER_TYPE_NOT_ALLOWED';
    case ReplayDetected        = 'REPLAY_DETECTED';
    case RateLimited           = 'RATE_LIMITED';
    case Unauthorized          = 'UNAUTHORIZED';
    case ServerError           = 'SERVER_ERROR';

    public function httpStatus(): int
    {
        return match ($this) {
            self::ValidationFailed     => 422,
            self::LicenseNotFound,
            self::InstallationNotFound => 404,
            self::Unauthorized         => 401,
            self::RateLimited          => 429,
            self::ServerError          => 500,
            self::Blacklisted,
            self::LicenseKilled,
            self::LicenseSuspended,
            self::LicenseInactive,
            self::LicenseExpired,
            self::ActivationLimit,
            self::DomainMismatch,
            self::DomainLocked,
            self::InstallationMismatch,
            self::ServerTypeNotAllowed,
            self::ReplayDetected       => 403,
        };
    }
}
