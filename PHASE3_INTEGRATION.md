# MRH License Server — Phase 3 (Admin Management Layer)

Install path: `C:\xampp\htdocs\saudi-license-server`
Stack: Laravel 12 · PHP 8.3+ · MySQL · Bootstrap 5 · jQuery · DataTables · Spatie Permission

## What Phase 3 delivers

Seven fully-wired admin modules with the Controller → Service → Repository pattern,
Form Request validation, Spatie-backed Policies, AJAX CRUD, and server-side DataTables.

| Module            | Controller | Service | Views |
|-------------------|-----------|---------|-------|
| Customers         | CustomerController | CustomerService | index + form modal |
| Licenses          | LicenseController | LicenseService, LicenseKeyService | index + form + action modals |
| Activation Logs   | ActivationLogController | — (read-only) | index |
| Verification Logs | VerificationLogController | — (read-only) | index |
| License Resets    | LicenseResetController | LicenseResetService | index |
| Blacklist         | BlacklistController | BlacklistService | index + modal |
| Audit Trail       | AuditLogController | — (+ chain verify) | index + detail modal |

## Folder structure (Phase 3 additions)

```
app/
├── Http/
│   ├── Controllers/Admin/
│   │   ├── CustomerController.php
│   │   ├── LicenseController.php
│   │   ├── ActivationLogController.php
│   │   ├── VerificationLogController.php
│   │   ├── LicenseResetController.php
│   │   ├── BlacklistController.php
│   │   └── AuditLogController.php
│   └── Requests/
│       ├── Customer/{Store,Update}CustomerRequest.php
│       ├── License/{Store,Update,Kill}LicenseRequest.php
│       ├── Blacklist/StoreBlacklistRequest.php
│       └── Reset/StoreResetRequest.php
├── Services/
│   ├── CustomerService.php
│   ├── LicenseService.php
│   ├── LicenseKeyService.php
│   ├── LicenseResetService.php
│   └── BlacklistService.php
├── Repositories/
│   ├── Contracts/{Repository,CustomerRepository,LicenseRepository}Interface.php
│   ├── BaseRepository.php
│   ├── CustomerRepository.php
│   └── LicenseRepository.php
├── Policies/
│   ├── CustomerPolicy.php
│   ├── LicensePolicy.php
│   ├── LicenseBlacklistPolicy.php
│   └── AuditLogPolicy.php
├── Providers/
│   ├── RepositoryServiceProvider.php
│   └── AuthServiceProvider.php
└── Support/
    ├── DomainNormalizer.php
    └── AuditLogger.php

routes/admin.php
database/seeders/PermissionSeeder.php
resources/views/
├── layouts/admin.blade.php
└── admin/
    ├── partials/sidebar.blade.php
    ├── customers/{index,form-modal}.blade.php
    ├── licenses/{index,form-modal,action-modals}.blade.php
    ├── activation-logs/index.blade.php
    ├── verification-logs/index.blade.php
    ├── resets/index.blade.php
    ├── blacklists/index.blade.php
    └── audit-logs/index.blade.php
```

## Wiring instructions

### 1. Register providers (bootstrap/providers.php)
```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\RepositoryServiceProvider::class,
];
```

### 2. Register admin routes (bootstrap/app.php)
```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    then: function () {
        Route::middleware('web')
            ->prefix('admin')
            ->name('admin.')
            ->group(base_path('routes/admin.php'));
    },
)
```

### 3. User model — add Spatie trait
```php
use Spatie\Permission\Traits\HasRoles;
class User extends Authenticatable { use HasRoles; /* ... */ }
```

### 4. Install + migrate + seed
```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate --seed
```

## Design & security notes

- **One-time key reveal**: issuing a license returns the plaintext key once via a
  static-backdrop modal with copy-to-clipboard; it is never retrievable again
  (only the encrypted copy + SHA-256 hash are stored).
- **Kill switch**: revokes all active activations and flags the license; blocks on next verify.
- **Reset**: clears activations, rotates the RSA key version, re-enables activation — all audited.
- **Audit chain**: every mutating service call appends a hash-chained AuditLog row;
  the Audit Trail view exposes a one-click full-chain integrity check.
- **Server-side DataTables**: all tables paginate/search/filter on the server for scale.
- **Central AJAX handling**: CSRF auto-injected; a shared error handler surfaces
  422 validation errors inline and other failures as toasts.
- **RBAC**: four roles (Super Admin, License Manager, Support Agent, Auditor) seeded
  with granular permissions enforced through policies on every controller action.
