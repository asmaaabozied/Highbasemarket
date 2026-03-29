<?php

namespace App\Policies;

use App\Models\Claim;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClaimPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Claim $claim): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Claim $claim): bool
    {
        return true;
    }

    public function delete(User $user, Claim $claim): bool
    {
        return true;
    }

    public function restore(User $user, Claim $claim): bool
    {
        return true;
    }

    public function forceDelete(User $user, Claim $claim): bool
    {
        return true;
    }
}
