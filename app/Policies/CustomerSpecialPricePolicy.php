<?php

namespace App\Policies;

use App\Models\CustomerSpecialPrice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerSpecialPricePolicy
{
    use HandlesAuthorization;

    public function create(User $user): bool
    {
        return $user->hasPermission('assign special price template');
    }

    public function delete(User $user, CustomerSpecialPrice $customerSpecialPrice): bool
    {
        if ($user->isAdmin() || $customerSpecialPrice->branch_id === currentBranch()->id) {
            return $user->hasPermission('remove special price template');
        }

        return false;
    }
}
