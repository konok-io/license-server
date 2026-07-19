<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Generates and hashes license keys.
 * Plaintext is returned once to the caller and never persisted directly;
 * the License model stores an encrypted copy + a SHA-256 hash for lookup.
 */
class LicenseKeyService
{
    private const PREFIX = 'SLS';

    /** @return array{plain:string, hash:string, prefix:string} */
    public function generate(): array
    {
        $segments = collect(range(1, 4))
            ->map(fn (): string => Str::upper(Str::random(4)))
            ->implode('-');

        $plain = self::PREFIX . '-' . $segments;

        return [
            'plain'  => $plain,
            'hash'   => $this->hash($plain),
            'prefix' => Str::substr($plain, 0, 8),
        ];
    }

    public function hash(string $plain): string
    {
        return hash('sha256', $plain);
    }
}
