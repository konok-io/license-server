<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

class DomainNormalizer
{
    /**
     * Normalize a domain for consistent locking/matching:
     * lowercase, trim scheme, strip leading www., drop path/port.
     */
    public static function normalize(?string $domain): ?string
    {
        if ($domain === null || trim($domain) === '') {
            return null;
        }

        $value = Str::lower(trim($domain));
        $value = preg_replace('#^https?://#', '', $value) ?? $value;
        $value = preg_replace('#^www\.#', '', $value) ?? $value;
        // Drop any path, query, or port segment.
        $value = explode('/', $value)[0];
        $value = explode(':', $value)[0];

        return $value !== '' ? $value : null;
    }

    public static function isWildcard(?string $domain): bool
    {
        return $domain !== null && str_starts_with($domain, '*.');
    }
}
