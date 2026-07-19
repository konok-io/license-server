<?php

declare(strict_types=1);

namespace App\Enums;

enum LicenseType: string
{
    case Localhost = 'localhost';
    case Domain    = 'domain';
    case Vps       = 'vps';

    public function label(): string
    {
        return match ($this) {
            self::Localhost => 'Localhost',
            self::Domain    => 'Domain',
            self::Vps       => 'VPS',
        };
    }
}
