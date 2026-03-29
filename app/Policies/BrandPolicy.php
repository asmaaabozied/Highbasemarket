<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BrandPolicy
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
        return $user->hasPermission('view all brands');
    }

    public function view(User $user, Brand $brand): bool
    {
        if ($user->hasPermission('view brand') && $user->isAdmin()) {
            return true;
        }

        return $user->hasPermission('view brand') && $brand->canAccess(currentBranch());
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create brand');
    }

    public function update(User $user, Brand $brand): bool
    {
        if ($user->hasPermission('update brand') && $user->isAdmin()) {
            return true;
        }

        return $user->hasPermission('update brand') && $brand->canAccess(currentBranch());
    }

    public function delete(User $user, Brand $brand): bool
    {
        if ($user->hasPermission('delete brand') && $user->isAdmin()) {
            return true;
        }

        return $user->hasPermission('delete brand') && $brand->canAccess(currentBranch());
    }

    public function restore(User $user, Brand $brand): bool
    {
        if ($user->hasPermission('restore brand') && $user->isAdmin()) {
            return true;
        }

        return $user->hasPermission('restore brand') && $brand->canAccess(currentBranch());
    }

    public function forceDelete(User $user, Brand $brand): bool
    {
        if ($user->hasPermission('forceDelete brand') && $user->isAdmin()) {
            return true;
        }

        return $user->hasPermission('forceDelete brand') && $brand->canAccess(currentBranch());
    }
}
