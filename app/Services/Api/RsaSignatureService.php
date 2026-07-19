<?php

declare(strict_types=1);

namespace App\Services\Api;


/**
 * Signs canonical payloads with the server's RSA private key. ERP clients
 * verify using the bundled public key. Private key material lives outside the
 * web root and is referenced via config('license.rsa').
 *
 * config/license.php (example):
 *   'rsa' => [
 *       'private_key_path' => storage_path('keys/private.pem'),
 *       'public_key_path'  => storage_path('keys/public.pem'),
 *       'passphrase'       => env('LICENSE_RSA_PASSPHRASE'),
 *       'algorithm'        => OPENSSL_ALGO_SHA256,
 *       'key_version'      => 'v1',
 *   ],
 */
class RsaSignatureService
{
    /**
     * Produce a base64 RSA signature over the canonical form of $payload.
     */
    public function sign(array $payload): string
    {
        $privateKey = $this->loadPrivateKey();
        $canonical  = $this->canonicalize($payload);

        $signature = '';
        $ok = openssl_sign(
            $canonical,
            $signature,
            $privateKey,
            (int) config('license.rsa.algorithm', OPENSSL_ALGO_SHA256),
        );

        if (! $ok) {
            throw new \RuntimeException('Failed to sign license payload: ' . openssl_error_string());
        }

        return base64_encode($signature);
    }

    /**
     * Verify a signature server-side (useful for tests / round-trips).
     */
    public function verify(array $payload, string $base64Signature): bool
    {
        $publicKey = $this->loadPublicKey();
        $canonical = $this->canonicalize($payload);

        return openssl_verify(
            $canonical,
            base64_decode($base64Signature, true) ?: '',
            $publicKey,
            (int) config('license.rsa.algorithm', OPENSSL_ALGO_SHA256),
        ) === 1;
    }

    public function keyVersion(): string
    {
        return (string) config('license.rsa.key_version', 'v1');
    }

    /**
     * Deterministic serialization: recursively ksort so key ordering can never
     * change the signed bytes. Clients must reproduce this exact form.
     */
    public function canonicalize(array $payload): string
    {
        $normalize = function (array $data) use (&$normalize): array {
            ksort($data);
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = $normalize($value);
                }
            }

            return $data;
        };

        return json_encode(
            $normalize($payload),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION,
        ) ?: '';
    }

    /** @return \OpenSSLAsymmetricKey */
    private function loadPrivateKey()
    {
        $path = (string) config('license.rsa.private_key_path');

        if ($path === '' || ! is_readable($path)) {
            throw new \RuntimeException('RSA private key not found or unreadable.');
        }

        $key = openssl_pkey_get_private(
            file_get_contents($path) ?: '',
            config('license.rsa.passphrase'),
        );

        if ($key === false) {
            throw new \RuntimeException('Invalid RSA private key: ' . openssl_error_string());
        }

        return $key;
    }

    /** @return \OpenSSLAsymmetricKey */
    private function loadPublicKey()
    {
        $path = (string) config('license.rsa.public_key_path');

        if ($path === '' || ! is_readable($path)) {
            throw new \RuntimeException('RSA public key not found or unreadable.');
        }

        $key = openssl_pkey_get_public(file_get_contents($path) ?: '');

        if ($key === false) {
            throw new \RuntimeException('Invalid RSA public key: ' . openssl_error_string());
        }

        return $key;
    }
}
