<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view all admins');
    }

    public function view(User $user, Admin $admin): bool
    {
        return $user->hasPermission('view admin');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create admin');
    }

    public function update(User $user, Admin $admin): bool
    {
        if ($admin->user->isSuperAdmin() && ! $user->isSuperAdmin()) {
            return false;
        }

        return $user->hasPermission('update admin');
    }

    public function delete(User $user, Admin $admin): bool
    {
        if ($admin->user->isSuperAdmin() && ! $user->isSuperAdmin()) {
            return false;
        }

        return $user->hasPermission('delete admin');
    }

    public function restore(User $user, Admin $admin): bool
    {
        return true;
    }

    public function forceDelete(User $user, Admin $admin): bool
    {
        return true;
    }

    public function updateStatus(User $user, Admin $admin): bool
    {
        return $user->hasPermission('update admin status');
    }

    public function updateRoles(User $user, Admin $admin, array $roles): bool
    {
        if ($admin->user->isSuperAdmin() && ! $user->isSuperAdmin()) {
            return false;
        }

        $roles = Role::whereIn('id', $roles);

        foreach ($roles as $role) {
            if ($role->roleable_id || $role->roleable_type) {
                return false;
            }
        }

        return $user->hasPermission('update admin role');
    }
}
