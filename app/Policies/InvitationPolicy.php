<?php

namespace App\Policies;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvitationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Invitation $invitation): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Invitation $invitation): bool
    {
        return true;
    }

    public function delete(User $user, Invitation $invitation): bool
    {
        return true;
    }

    public function restore(User $user, Invitation $invitation): bool
    {
        return true;
    }

    public function forceDelete(User $user, Invitation $invitation): bool
    {
        return true;
    }
}
