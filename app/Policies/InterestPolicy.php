<?php

namespace App\Policies;

use App\Models\Interest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InterestPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view all interests');
    }

    public function view(User $user, Interest $interest): bool
    {
        return $user->hasPermission('view interest') && $interest->canBeAccessedBy($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create interest');
    }

    public function update(User $user, Interest $interest): bool
    {
        return $user->hasPermission('update interest') && $interest->canBeAccessedBy($user);
    }

    public function delete(User $user, Interest $interest): bool
    {
        return $user->hasPermission('delete interest') && $interest->canBeAccessedBy($user);
    }

    public function restore(User $user, Interest $interest): bool
    {
        return $user->hasPermission('restore interest') && $interest->canBeAccessedBy($user);
    }

    public function forceDelete(User $user, Interest $interest): bool
    {
        return $user->hasPermission('forceDelete interest') && $interest->canBeAccessedBy($user);
    }
}
