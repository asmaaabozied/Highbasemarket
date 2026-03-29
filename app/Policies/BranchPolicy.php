<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BranchPolicy
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
        return $user->hasPermission('view all branches');
    }

    public function view(User $user, Branch $branch): bool
    {
        if ($user->isAdmin()) {
            return $user->hasPermission('view branch');
        }

        return $user->hasPermission('view branch')
            && $branch->canBeAccessedBy($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create branch');
    }

    public function update(User $user, Branch $branch): bool
    {
        if ($user->isAdmin()) {
            return $user->hasPermission('update branch');
        }

        return $user->hasPermission('update branch')
            && $branch->canBeAccessedBy($user);
    }

    public function delete(User $user, Branch $branch): bool
    {
        if ($user->isAdmin()) {
            return $user->hasPermission('delete branch');
        }

        return $user->hasPermission('delete branch')
            && $branch->canBeAccessedBy($user);
    }

    public function restore(User $user, Branch $branch): bool
    {
        return $user->hasPermission('restore branch')
            && $branch->canBeAccessedBy($user);
    }

    public function forceDelete(User $user, Branch $branch): bool
    {
        return $user->hasPermission('forceDelete branch')
            && $branch->canBeAccessedBy($user);
    }

    public function updateStatus(User $user, Branch $branch): bool
    {
        return $user->hasPermission('updateStatus branch')
            && $branch->canBeAccessedBy($user);
    }

    public function visitStore(User $user, Branch $branch): bool
    {
        if ($user->isAdmin() || $branch->account_id === currentBranch()->account_id) {
            return true;
        }

        return currentBranch()->isCustomerOf($branch);
    }
}
