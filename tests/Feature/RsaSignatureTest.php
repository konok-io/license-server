<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Api\RsaSignatureService;
use Tests\TestCase;

/**
 * Verifies the RSA sign → verify round-trip and tamper rejection.
 * Requires keys to exist (php artisan license:generate-keys).
 */
class RsaSignatureTest extends TestCase
{
    public function test_sign_and_verify_round_trip(): void
    {
        if (! is_readable((string) config('license.rsa.private_key_path'))) {
            $this->markTestSkipped('RSA keys not generated; run php artisan license:generate-keys.');
        }

        $signer = app(RsaSignatureService::class);
        $payload = ['action' => 'continue', 'license_uuid' => 'test-uuid', 'nonce' => 123];

        $signature = $signer->sign($payload);

        $this->assertTrue($signer->verify($payload, $signature));
    }

    public function test_tampered_payload_fails_verification(): void
    {
        if (! is_readable((string) config('license.rsa.private_key_path'))) {
            $this->markTestSkipped('RSA keys not generated.');
        }

        $signer = app(RsaSignatureService::class);
        $signature = $signer->sign(['action' => 'continue']);

        $this->assertFalse($signer->verify(['action' => 'kill'], $signature));
    }
}
