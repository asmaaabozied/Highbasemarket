<?php

namespace App\Policies;

use App\Models\User;

class VisitPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function before(User $user, $ability): ?bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any visits.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view all visits');
    }
}
