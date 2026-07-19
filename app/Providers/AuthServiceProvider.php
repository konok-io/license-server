<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\License;
use App\Models\LicenseBlacklist;
use App\Policies\AuditLogPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\LicenseBlacklistPolicy;
use App\Policies\LicensePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Registers model → policy mappings explicitly via Gate::policy().
 * (Laravel 12-native; avoids the deprecated foundation AuthServiceProvider.)
 */
class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(License::class, LicensePolicy::class);
        Gate::policy(LicenseBlacklist::class, LicenseBlacklistPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
    }
}
