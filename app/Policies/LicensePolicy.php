<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\License;
use App\Models\User;

class LicensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('licenses.view');
    }

    public function view(User $user, License $license): bool
    {
        return $user->can('licenses.view');
    }

    public function create(User $user): bool
    {
        return $user->can('licenses.create');
    }

    public function update(User $user, License $license): bool
    {
        return $user->can('licenses.update');
    }

    public function delete(User $user, License $license): bool
    {
        return $user->can('licenses.delete');
    }

    public function kill(User $user, ?License $license = null): bool
    {
        return $user->can('licenses.kill');
    }

    public function reset(User $user, ?License $license = null): bool
    {
        return $user->can('licenses.reset');
    }
}
