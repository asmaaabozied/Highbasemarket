<?php

namespace App\Policies;

use App\Models\NewsFeed;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NewsFeedPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return $user->hasPermission('view all news feeds');
    }

    public function view(User $user, NewsFeed $newsFeed): bool
    {
        return ($user->isAdministrator() || $user->hasPermission('view news feed'))
            && $newsFeed->canBeAccessedBy($user);
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdministrator()) {
            return true;
        }

        return $user->hasPermission('create news feed');
    }

    public function update(User $user, NewsFeed $newsFeed): bool
    {
        return ($user->isAdministrator() || $user->hasPermission('update news feed'))
            && $newsFeed->canBeAccessedBy($user);
    }

    public function delete(User $user, NewsFeed $newsFeed): bool
    {
        return ($user->isAdministrator() || $user->hasPermission('delete news feed'))
            && $newsFeed->canBeAccessedBy($user);
    }

    public function restore(User $user, NewsFeed $newsFeed): bool
    {
        return ($user->isAdministrator() || $user->hasPermission('restore news feed'))
            && $newsFeed->canBeAccessedBy($user);
    }

    public function forceDelete(User $user, NewsFeed $newsFeed): bool
    {
        return ($user->isAdministrator() || $user->hasPermission('forceDelete news feed'))
            && $newsFeed->canBeAccessedBy($user);
    }
}
