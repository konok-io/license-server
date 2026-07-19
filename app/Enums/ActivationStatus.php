<?php

declare(strict_types=1);

namespace App\Enums;

enum ActivationStatus: string
{
    case Active  = 'active';
    case Revoked = 'revoked';
    case Expired = 'expired';
}
