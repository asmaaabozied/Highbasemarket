<?php

namespace App\Policies;

use App\Models\SpecialPriceTemplate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SpecialPriceTemplatePolicy
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
        return $user->hasPermission('view all special price templates');
    }

    public function view(User $user, SpecialPriceTemplate $specialPriceTemplate): bool
    {
        if ($user->isAdmin()) {
            return $user->hasPermission('view special price template');
        }

        return $user->hasPermission('view special price template')
            && $specialPriceTemplate->canBeAccessedBy($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create special price template');
    }

    public function update(User $user, SpecialPriceTemplate $specialPriceTemplate): bool
    {
        if ($user->isAdmin()) {
            return $user->hasPermission('update special price template');
        }

        return $user->hasPermission('update special price template')
            && $specialPriceTemplate->canBeAccessedBy($user);
    }

    public function delete(User $user, SpecialPriceTemplate $specialPriceTemplate): bool
    {
        if ($user->isAdmin()) {
            return $user->hasPermission('delete special price template');
        }

        return $user->hasPermission('delete special price template')
            && $specialPriceTemplate->canBeAccessedBy($user);
    }

    public function restore(User $user, SpecialPriceTemplate $specialPriceTemplate): bool
    {
        if ($user->isAdmin()) {
            return $user->hasPermission('restore special price template');
        }

        return $user->hasPermission('restore special price template')
            && $specialPriceTemplate->canBeAccessedBy($user);
    }

    public function forceDelete(User $user, SpecialPriceTemplate $specialPriceTemplate): bool
    {
        if ($user->isAdmin()) {
            return $user->hasPermission('force delete special price template');
        }

        return $user->hasPermission('force delete special price template')
            && $specialPriceTemplate->canBeAccessedBy($user);
    }
}
