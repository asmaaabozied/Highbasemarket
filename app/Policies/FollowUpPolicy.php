<?php

namespace App\Policies;

use App\Models\FollowUp;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FollowUpPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;

    }

    public function view(User $user, FollowUp $followUp): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FollowUp $followUp): bool
    {
        return true;
    }

    public function delete(User $user, FollowUp $followUp): bool
    {
        return true;
    }

    public function restore(User $user, FollowUp $followUp): bool
    {
        return true;
    }

    public function forceDelete(User $user, FollowUp $followUp): bool
    {
        return true;
    }
}
