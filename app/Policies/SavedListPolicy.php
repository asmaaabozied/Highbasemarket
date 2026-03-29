<?php

namespace App\Policies;

use App\Models\SavedList;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SavedListPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SavedList $savedList): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, SavedList $savedList): bool
    {
        return true;
    }

    public function delete(User $user, SavedList $savedList): bool
    {
        return true;
    }

    public function restore(User $user, SavedList $savedList): bool
    {
        return true;
    }

    public function forceDelete(User $user, SavedList $savedList): bool
    {
        return true;
    }
}
