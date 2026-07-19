<?php

declare(strict_types=1);

namespace App\Enums;

enum LicenseStatus: string
{
    case Pending   = 'pending';
    case Active    = 'active';
    case Suspended = 'suspended';
    case Expired   = 'expired';
    case Killed    = 'killed';
    case Reset     = 'reset';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Pending',
            self::Active    => 'Active',
            self::Suspended => 'Suspended',
            self::Expired   => 'Expired',
            self::Killed    => 'Killed',
            self::Reset     => 'Reset',
        };
    }

    /** Whether the license is in a state that permits activation/verification. */
    public function isUsable(): bool
    {
        return $this === self::Active;
    }
}
