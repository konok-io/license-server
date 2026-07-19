<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LicenseBlacklist;
use App\Models\User;

class LicenseBlacklistPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('blacklists.view');
    }

    public function create(User $user): bool
    {
        return $user->can('blacklists.create');
    }

    /**
     * $blacklist is nullable so class-level checks work in Blade
     * (@can('update', LicenseBlacklist::class)) as well as instance checks.
     */
    public function update(User $user, ?LicenseBlacklist $blacklist = null): bool
    {
        return $user->can('blacklists.update');
    }

    public function delete(User $user, ?LicenseBlacklist $blacklist = null): bool
    {
        return $user->can('blacklists.delete');
    }
}
