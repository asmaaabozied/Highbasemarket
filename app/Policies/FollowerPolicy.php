<?php

namespace App\Policies;

use App\Models\Follower;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FollowerPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view all followers');
    }

    public function view(User $user, Follower $follower): bool
    {
        return $user->hasPermission('view follower') && $user->getAccount()->id === $follower->branch->account_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create follower');
    }

    public function update(User $user, Follower $follower): bool
    {
        return $user->hasPermission('update follower') && $user->getAccount()->id === $follower->branch->account_id;
    }

    public function delete(User $user, Follower $follower): bool
    {
        return $user->hasPermission('delete follower') && $user->getAccount()->id === $follower->branch->account_id;
    }

    public function restore(User $user, Follower $follower): bool
    {
        return true;
    }

    public function forceDelete(User $user, Follower $follower): bool
    {
        return true;
    }
}
