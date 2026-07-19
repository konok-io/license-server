<?php

declare(strict_types=1);

namespace App\Enums;

enum VerificationResult: string
{
    case Success         = 'success';
    case Failed          = 'failed';
    case Killed          = 'killed';
    case Expired         = 'expired';
    case DomainMismatch  = 'domain_mismatch';
    case InstallMismatch = 'install_mismatch';
    case Blacklisted     = 'blacklisted';
}
