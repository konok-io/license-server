# Saudi License Server — Phase 5 (Daily Verification + Signed Responses)

Install path: `C:\xampp\htdocs\saudi-license-server`
Stack: Laravel 12 · PHP 8.3+ · MySQL · OpenSSL RSA-4096

## Endpoint

| Method | Path            | Purpose |
|--------|-----------------|---------|
| POST   | /api/verify     | Daily verification heartbeat → RSA-4096 signed verdict |

Available at `/api/v1/verify` and `/api/verify`.

## The signed-verdict protocol

1. The ERP client calls `POST /api/verify` on its schedule (every
   `verify_interval` hours, default 24).
2. The server evaluates license + installation state and decides an **action**.
3. The server builds a canonical payload, signs it with **private.pem**
   (`openssl_sign`, SHA-256, RSA-4096), and returns it as `signature`.
4. The client verifies the signature against **public.pem**
   (`openssl_verify`) BEFORE acting. A verdict that fails verification is
   never trusted — the client keeps its last known good state.

Because the verdict is signed, a man-in-the-middle cannot forge a kill, forge a
pass, or downgrade `continue`→`kill`. (Verified: tampering the payload makes
`openssl_verify` return failure.)

## Request

```json
{
  "license_key": "SLS-XXXX-XXXX-XXXX-XXXX",
  "installation_id": "INST-9F3A…",
  "domain": "erp.client.sa",
  "nonce": "3f9a…"
}
```

## Response (signed)

```json
{
  "success": true,
  "data": {
    "verified": true,
    "verdict": {
      "license_uuid": "…",
      "installation_id": "INST-9F3A…",
      "action": "continue",
      "result": "success",
      "operational": true,
      "status": "active",
      "expires_at": "2027-01-01T00:00:00+00:00",
      "grace_days": 3,
      "verify_interval": 24,
      "next_verify_by": "2026-07-09T12:00:00+00:00",
      "key_version": "v1",
      "verified_at": "2026-07-08T12:00:00+00:00"
    }
  },
  "signature": "base64-RSA-4096-signature",
  "server_time": "2026-07-08T12:00:00+00:00"
}
```

The client runs `openssl_verify(canonical(verdict), signature, public.pem)`.

## Verdict actions

| action      | HTTP | result           | Client behavior |
|-------------|------|------------------|-----------------|
| continue    | 200  | success          | Keep running |
| grace       | 200  | success          | Keep running; show expiry warning |
| kill        | 403  | killed           | Disable the ERP (remote kill switch) |
| expire      | 403  | expired          | Lock out (past grace window) |
| reactivate  | 403  | install_mismatch | Binding lost/revoked → re-activate |
| deny        | 403  | blacklisted/failed | Block (blacklist or suspended) |

Decision precedence (server): blacklist → kill switch → suspended →
installation binding → domain lock → expiry/grace → continue.

Note: negative verdicts still return a SIGNED body. The client should verify
the signature even on a 403 before enacting a kill, so it cannot be tricked by
a spoofed unsigned 403.

## Verification logging

Every call writes three records:

- **license_verifications** — immutable verification row with `result`, `nonce`,
  `payload_hash` (SHA-256 of the signed canonical payload), and `latency_ms`.
- **verification_logs** — transport-level snapshot with `kill_directive`,
  request/response payloads, IP, and user agent.
- **audit_logs** — hash-chained `license.verified` entry.

Side effects by action: continue/grace refresh `last_verified_at`; expire flips
license status to `expired`; kill revokes the active binding and recomputes
`activation_count`.

## RSA keys

Keys were pre-generated for you at `storage/keys/`:

```
storage/keys/private.pem   (RSA-4096, 0600, server only — NEVER distribute)
storage/keys/public.pem    (distribute with ERP clients)
```

**Regenerate for production** (the bundled keys are for local testing only):

```bash
php artisan license:generate-keys --bits=4096 --force
```

`.env`:
```
LICENSE_RSA_PRIVATE_KEY=/absolute/path/storage/keys/private.pem
LICENSE_RSA_PUBLIC_KEY=/absolute/path/storage/keys/public.pem
LICENSE_RSA_PASSPHRASE=change-me
LICENSE_RSA_KEY_VERSION=v1
```

## Client SDK

`client-sdk/LicenseClientVerifier.php` — drop into the ERP alongside
`public.pem`. It reconstructs the exact canonical bytes and validates the
signature with `openssl_verify`. Its `canonicalize()` MUST stay byte-for-byte
identical to `RsaSignatureService::canonicalize()` (recursive ksort +
`JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE`).

```php
$verifier = new LicenseClientVerifier(__DIR__ . '/public.pem');
$action = $verifier->trustedAction($response['data']['verdict'], $response['signature']);
// null if the signature is invalid → do NOT trust the verdict.
```

## Files added in Phase 5

```
app/Enums/VerificationAction.php
app/Services/Api/VerificationService.php
app/Http/Requests/Api/VerifyRequest.php
app/Http/Controllers/Api/V1/VerificationController.php
routes/api.php                       (verify route added)
storage/keys/{private,public}.pem    (RSA-4096)
client-sdk/LicenseClientVerifier.php
client-sdk/public.pem
```

## Proven

- RSA-4096 keypair generated; signature length 512 bytes (= 4096-bit).
- `openssl_sign` (server) → `openssl_verify` (client) round-trip: Verified OK.
- Tampered payload (continue→kill) → verification failure (rejected).
