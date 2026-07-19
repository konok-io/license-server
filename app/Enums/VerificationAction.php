<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The directive returned to the ERP client inside the signed verify response.
 * The client acts on this action, and can trust it because the payload is
 * RSA-4096 signed.
 */
enum VerificationAction: string
{
    case Continue    = 'continue';     // Valid — keep running.
    case Kill        = 'kill';         // Remote kill switch — disable the ERP.
    case Grace       = 'grace';        // Expired but within grace window — warn.
    case Expire      = 'expire';       // Past grace — lock out.
    case Reactivate  = 'reactivate';   // Binding lost — client must re-activate.
    case Deny        = 'deny';         // Blacklisted / suspended — block.

    public function label(): string
    {
        return match ($this) {
            self::Continue   => 'Continue',
            self::Kill       => 'Kill',
            self::Grace      => 'Grace period',
            self::Expire     => 'Expired',
            self::Reactivate => 'Re-activation required',
            self::Deny       => 'Access denied',
        };
    }

    /** Whether the ERP should remain operational under this action. */
    public function isOperational(): bool
    {
        return in_array($this, [self::Continue, self::Grace], true);
    }
}
