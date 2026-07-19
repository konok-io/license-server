<?php

declare(strict_types=1);

namespace App\Enums;

enum AuditEvent: string
{
    case LicenseIssued     = 'license.issued';
    case LicenseActivated  = 'license.activated';
    case LicenseVerified   = 'license.verified';
    case LicenseKilled     = 'license.killed';
    case LicenseReset      = 'license.reset';
    case LicenseSuspended  = 'license.suspended';
    case LicenseExpired    = 'license.expired';
    case Blacklisted       = 'license.blacklisted';
    case ActivationRevoked = 'activation.revoked';
}
