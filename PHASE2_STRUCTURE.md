# Saudi License Server — Phase 2 Deliverables

Install path: `C:\xampp\htdocs\saudi-license-server`
Stack: Laravel 12 · PHP 8.3+ · MySQL

## Folder Structure (Phase 2 files)

```
saudi-license-server/
├── app/
│   ├── Enums/
│   │   ├── LicenseStatus.php          (pending/active/suspended/expired/killed/reset)
│   │   ├── LicenseType.php            (localhost/domain/vps)
│   │   ├── ActivationStatus.php       (active/revoked/expired)
│   │   ├── VerificationResult.php     (success/failed/killed/…)
│   │   └── AuditEvent.php             (license.* / activation.* events)
│   └── Models/
│       ├── Customer.php
│       ├── License.php
│       ├── LicenseActivation.php
│       ├── LicenseVerification.php
│       ├── LicenseReset.php
│       ├── LicenseBlacklist.php
│       ├── ActivationLog.php
│       ├── VerificationLog.php
│       └── AuditLog.php               (immutable, hash-chained)
└── database/
    ├── migrations/
    │   ├── 2025_01_01_000001_create_customers_table.php
    │   ├── 2025_01_01_000002_create_licenses_table.php
    │   ├── 2025_01_01_000003_create_license_activations_table.php
    │   ├── 2025_01_01_000004_create_license_verifications_table.php
    │   ├── 2025_01_01_000005_create_license_resets_table.php
    │   ├── 2025_01_01_000006_create_license_blacklists_table.php
    │   ├── 2025_01_01_000007_create_activation_logs_table.php
    │   ├── 2025_01_01_000008_create_verification_logs_table.php
    │   └── 2025_01_01_000009_create_audit_logs_table.php
    ├── factories/
    │   ├── CustomerFactory.php
    │   ├── LicenseFactory.php          (+ localhost/domain/vps/killed/expired/… states)
    │   ├── LicenseActivationFactory.php
    │   ├── LicenseVerificationFactory.php
    │   ├── LicenseResetFactory.php
    │   ├── LicenseBlacklistFactory.php
    │   ├── ActivationLogFactory.php
    │   ├── VerificationLogFactory.php
    │   └── AuditLogFactory.php
    └── seeders/
        ├── DatabaseSeeder.php          (orchestrator — ordered)
        ├── CustomerSeeder.php
        ├── LicenseSeeder.php           (licenses + activations + verifications + logs + audit)
        └── BlacklistSeeder.php
```

## Relationship Map

- Customer 1─∞ License
- License 1─∞ LicenseActivation 1─∞ LicenseVerification
- License 1─∞ LicenseReset
- License 1─∞ LicenseBlacklist
- License 1─∞ ActivationLog / VerificationLog
- AuditLog ∞─1 (polymorphic) any auditable model

## Key Design Decisions

1. **License keys never stored in plaintext** — `license_key_encrypted` (Laravel `encrypted` cast) + indexed `license_key_hash` (SHA-256) for O(1) lookups + `license_key_prefix` for display.
2. **Enums as casts** for type/status safety (Laravel 12 native backed enums).
3. **UUID + auto-increment id** — public UUID for external references, integer PK internally.
4. **Immutable audit trail** — `AuditLog` blocks update/delete and hash-chains rows (`previous_hash` → `hash`) for tamper-evidence.
5. **Denormalized `activation_count`** on licenses, reconciled by seeder / future services.
6. **Composite unique** `(license_id, installation_id)` enforces Installation ID Lock.
7. **Soft deletes** on customers & licenses; hard integrity elsewhere.
8. **Laravel 12 conventions** — `declare(strict_types=1)`, `casts()` method (not property), typed relationships with generics docblocks, anonymous-class migrations.

## Run Commands

```bash
php artisan migrate
php artisan db:seed
# or
php artisan migrate --seed
```
