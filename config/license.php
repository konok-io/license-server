<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | RSA Signing
    |--------------------------------------------------------------------------
    | Private key signs activation/verification payloads; the public key is
    | bundled with ERP clients so they can verify grants offline. Keep the
    | private key OUTSIDE the web root (storage/keys is git-ignored).
    */
    'rsa' => [
        'private_key_path' => env('LICENSE_RSA_PRIVATE_KEY', storage_path('keys/private.pem')),
        'public_key_path'  => env('LICENSE_RSA_PUBLIC_KEY', storage_path('keys/public.pem')),
        'passphrase'       => env('LICENSE_RSA_PASSPHRASE'),
        'algorithm'        => OPENSSL_ALGO_SHA256,
        'key_version'      => env('LICENSE_RSA_KEY_VERSION', 'v1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    | Requests per minute per IP for the license API.
    */
    'rate_limit' => [
        'per_minute' => (int) env('LICENSE_API_RATE_LIMIT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Replay Window
    |--------------------------------------------------------------------------
    */
    'replay_ttl_seconds' => (int) env('LICENSE_REPLAY_TTL', 300),
];
