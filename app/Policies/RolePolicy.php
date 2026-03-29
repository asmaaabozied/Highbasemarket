<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin() || $user->userable->job_title === 'administrator') {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view all roles');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasPermission('view role');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create role');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasPermission('update role');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermission('delete role');
    }

    public function restore(User $user, Role $role): bool
    {
        return true;
    }

    public function forceDelete(User $user, Role $role): bool
    {
        return true;
    }
}
