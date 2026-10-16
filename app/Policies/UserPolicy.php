<?php

namespace App\Policies;

use App\Models\User;
use App\Role;

class UserPolicy
{
    /**
     * Only admins manage the staff list.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Admin);
    }

    /**
     * Admins can edit every account except an owner's.
     */
    public function update(User $user, User $target): bool
    {
        return $user->hasRole(Role::Admin) && ! $target->hasRole(Role::Owner);
    }
}
