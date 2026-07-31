<?php

declare(strict_types=1);

/**
 * MRH License Server — Client-Side Verifier (embed in the ERP)
 * -------------------------------------------------------------------------
 * Ships with the ERP alongside the vendor's PUBLIC key (public.pem only).
 * After calling POST /api/verify, the ERP passes the response's `data.verdict`
 * and `signature` here. This class reconstructs the exact canonical bytes the
 * server signed and validates them with openssl_verify() + the public key.
 *
 * The ERP must ACT on `verdict.action` only after the signature checks out.
 * An unsigned or invalid response should be treated as "retain last known
 * good state until the next successful verify" — never as an implicit kill,
 * and never as an implicit pass.
 */
final class LicenseClientVerifier
{
    public function __construct(
        private readonly string $publicKeyPath,
    ) {
    }

    /**
     * @param array<string,mixed> $verdict   The `data.verdict` object from /api/verify.
     * @param string              $signature Base64 signature from the response `signature` field.
     */
    public function isAuthentic(array $verdict, string $signature): bool
    {
        $publicKey = openssl_pkey_get_public(file_get_contents($this->publicKeyPath) ?: '');
        if ($publicKey === false) {
            return false;
        }

        $canonical = $this->canonicalize($verdict);
        $decoded   = base64_decode($signature, true);
        if ($decoded === false) {
            return false;
        }

        return openssl_verify($canonical, $decoded, $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }

    /**
     * Convenience: returns the action string only if the signature is valid,
     * otherwise null. The ERP branches on this.
     */
    public function trustedAction(array $verdict, string $signature): ?string
    {
        return $this->isAuthentic($verdict, $signature) ? ($verdict['action'] ?? null) : null;
    }

    /**
     * MUST byte-for-byte match RsaSignatureService::canonicalize() on the server:
     * recursive ksort, then json_encode with UNESCAPED_SLASHES | UNESCAPED_UNICODE.
     */
    private function canonicalize(array $payload): string
    {
        $normalize = function (array $data) use (&$normalize): array {
            ksort($data);
            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    $data[$k] = $normalize($v);
                }
            }
            return $data;
        };

        return json_encode(
            $normalize($payload),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
        ) ?: '';
    }
}

/*
 * -------------------------------------------------------------------------
 * Example usage inside the ERP (pseudo-flow):
 * -------------------------------------------------------------------------
 *
 * $response = http_post('https://license.vendor.sa/api/verify', [
 *     'license_key'     => $storedKey,
 *     'installation_id' => $installationId,
 *     'domain'          => $_SERVER['HTTP_HOST'] ?? null,
 *     'nonce'           => bin2hex(random_bytes(16)),
 * ]);
 *
 * $verifier = new LicenseClientVerifier(__DIR__ . '/public.pem');
 * $action   = $verifier->trustedAction($response['data']['verdict'], $response['signature']);
 *
 * match ($action) {
 *     'continue', 'grace' => null,                 // keep running (grace shows a warning)
 *     'kill', 'expire', 'deny' => disable_erp(),   // hard stop
 *     'reactivate'        => prompt_reactivation(),
 *     default             => keep_last_known_state(), // unsigned/unknown → do not trust
 * };
 */
