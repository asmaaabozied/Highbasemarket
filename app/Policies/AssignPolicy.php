<?php

namespace App\Policies;

use App\Models\Assign;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssignPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Assign $assign): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Assign $assign): bool
    {
        return true;
    }

    public function delete(User $user, Assign $assign): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $assign->assigner_id === $user->userable_id
        || ($assign->assigner->account_id === $user->getAccount()->id && $user->userable->job_title === 'administrator');
    }

    public function restore(User $user, Assign $assign): bool
    {
        return true;
    }

    public function forceDelete(User $user, Assign $assign): bool
    {
        return true;
    }
}
