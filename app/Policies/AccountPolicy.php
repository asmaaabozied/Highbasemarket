<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view all accounts');
    }

    public function view(User $user, Account $account): bool
    {
        return $user->hasPermission('view account');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create account');
    }

    public function update(User $user, Account $account): bool
    {
        return $user->hasPermission('update account');
    }

    public function delete(User $user, Account $account): bool
    {
        return $user->hasPermission('delete account');
    }

    public function restore(User $user, Account $account): bool
    {
        return $user->hasPermission('restore account');
    }

    public function forceDelete(User $user, Account $account): bool
    {
        return $user->hasPermission('forceDelete account');
    }
}
