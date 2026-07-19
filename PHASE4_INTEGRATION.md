# Saudi License Server — Phase 4 (Client-Facing License API)

Install path: `C:\xampp\htdocs\saudi-license-server`
Stack: Laravel 12 · PHP 8.3+ · MySQL · OpenSSL (RSA-4096)

## Endpoints

All accept `POST` with JSON, return the canonical envelope, and are available
both at `/api/v1/...` and `/api/...`.

| Method | Path                        | Purpose |
|--------|-----------------------------|---------|
| POST   | /api/activate               | Bind an installation to a license; returns a signed grant |
| POST   | /api/check-domain           | Confirm a domain is permitted (no slot consumed) |
| POST   | /api/check-installation     | Confirm an installation is still bound (heartbeat) |
| POST   | /api/reset                  | Release one or all installation bindings |

## Response envelope

```json
{
  "success": true,
  "data": { },
  "error": null,
  "signature": "base64-RSA-signature-or-null",
  "server_time": "2026-07-08T12:00:00+00:00"
}
```

Errors:
```json
{
  "success": false,
  "data": null,
  "error": { "code": "ACTIVATION_LIMIT_REACHED", "message": "…", "details": { } },
  "signature": null,
  "server_time": "…"
}
```

Clients branch on `error.code` (stable), never on `message`.

## Request / response examples

### POST /api/activate
```json
{
  "license_key": "SLS-XXXX-XXXX-XXXX-XXXX",
  "installation_id": "INST-9F3A…",
  "domain": "erp.client.sa",
  "server_type": "domain",
  "fingerprint": "sha256-hardware-hash",
  "os_info": "Windows Server 2022"
}
```
→ `201`
```json
{
  "success": true,
  "data": {
    "activated": true,
    "license_uuid": "…",
    "installation_id": "INST-9F3A…",
    "grant": {
      "license_uuid": "…", "installation_id": "…", "domain": "erp.client.sa",
      "type": "domain", "product": "saudi-manpower-erp", "max_activations": 3,
      "expires_at": "2027-01-01T00:00:00+00:00", "grace_days": 3,
      "verify_interval": 24, "key_version": "v1", "issued_at": "…"
    }
  },
  "signature": "base64…",
  "server_time": "…"
}
```
The client verifies `signature` against `grant` using the bundled public key,
then stores the grant locally for offline/daily checks.

### POST /api/check-domain
```json
{ "license_key": "SLS-…", "domain": "erp.client.sa", "server_type": "domain" }
```
→ `{ "data": { "domain_allowed": true, "domain": "erp.client.sa", "license_type": "domain" } }`

### POST /api/check-installation
```json
{ "license_key": "SLS-…", "installation_id": "INST-9F3A…" }
```
→ `{ "data": { "installation_valid": true, "status": "active", "expires_at": "…", "last_verified_at": "…" } }`

### POST /api/reset
```json
{ "license_key": "SLS-…", "installation_id": "INST-9F3A…", "reason": "Server migration" }
```
Omit `installation_id` to reset ALL bindings.
→ `{ "data": { "reset": true, "activations_cleared": 1, "remaining_activations": 0 } }`

## Error codes

VALIDATION_FAILED (422) · LICENSE_NOT_FOUND (404) · LICENSE_INACTIVE/EXPIRED/KILLED/SUSPENDED (403) ·
ACTIVATION_LIMIT_REACHED (403) · DOMAIN_MISMATCH/DOMAIN_LOCKED (403) ·
INSTALLATION_MISMATCH (403) · INSTALLATION_NOT_FOUND (404) · BLACKLISTED (403) ·
SERVER_TYPE_NOT_ALLOWED (403) · REPLAY_DETECTED (403) · RATE_LIMITED (429) ·
UNAUTHORIZED (401) · SERVER_ERROR (500)

## Validation logic (order of guards on /activate)

1. resolveByKey        — SHA-256 hash lookup, else LICENSE_NOT_FOUND
2. assertUsable        — active, not killed/suspended/expired
3. assertNotBlacklisted — license/install/domain/IP not blocked
4. assertServerTypeAllowed — localhost/domain/vps matches license type
5. assertDomainAllowed  — domain lock (with wildcard subdomain support)
6. assertActivationSlotAvailable — existing installs exempt; new binds respect max_activations

Re-activating the same `installation_id` is idempotent — it refreshes the
binding without consuming a new slot.

## Folder structure (Phase 4 additions)

```
app/
├── Console/Commands/GenerateRsaKeys.php
├── Enums/ApiErrorCode.php
├── Exceptions/Api/LicenseApiException.php
├── Http/
│   ├── Controllers/Api/V1/
│   │   ├── ActivationController.php
│   │   ├── DomainCheckController.php
│   │   ├── InstallationCheckController.php
│   │   └── ResetController.php
│   ├── Middleware/{PreventReplay,ForceJsonResponse}.php
│   └── Requests/Api/
│       ├── ActivateRequest.php
│       ├── CheckDomainRequest.php
│       ├── CheckInstallationRequest.php
│       └── ResetRequest.php
├── Services/Api/
│   ├── LicenseValidationService.php
│   ├── ActivationService.php
│   ├── ApiResetService.php
│   └── RsaSignatureService.php
└── Support/Api/{ApiResponse,ApiExceptionRenderer}.php
config/license.php
routes/api.php
```

## Wiring (bootstrap/app.php)

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    // + admin group from Phase 3
)
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'api.json'   => App\Http\Middleware\ForceJsonResponse::class,
        'api.replay' => App\Http\Middleware\PreventReplay::class,
    ]);
})
->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->render(function (Throwable $e, Illuminate\Http\Request $request) {
        return App\Support\Api\ApiExceptionRenderer::render($e, $request);
    });
})
```

## Rate limiter (AppServiceProvider::boot)

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('license-api', fn ($request) =>
    Limit::perMinute(config('license.rate_limit.per_minute', 60))->by($request->ip())
);
```

## Bootstrap the RSA keys

```bash
php artisan license:generate-keys --bits=4096
# → storage/keys/private.pem (0600, server-only)
#   storage/keys/public.pem  (bundle with ERP clients)
```

Add to `.env`:
```
LICENSE_RSA_PASSPHRASE=change-me-in-production
LICENSE_RSA_KEY_VERSION=v1
LICENSE_API_RATE_LIMIT=60
```

## Security properties

- License keys never travel or store in plaintext at rest — hashed for lookup.
- Grants are RSA-SHA256 signed over a canonical (recursively ksorted) JSON form,
  so clients detect any tampering offline. (Round-trip verified with OpenSSL.)
- Replay protection via one-time X-Nonce + optional X-Timestamp skew window.
- Per-IP throttling; JSON-only error rendering (no HTML/stack traces in prod).
- Every activation/reset writes an ActivationLog + hash-chained AuditLog entry.
