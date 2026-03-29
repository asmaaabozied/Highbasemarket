<?php

namespace App\Policies;

use App\Models\BankDetail;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BankDetailPolicy
{
    use HandlesAuthorization;

    public function create(User $user, ?Branch $branch = null): bool
    {
        if ($branch instanceof \App\Models\Branch) {
            return $branch->canBeAccessedBy($user) && $user->hasPermission('create bank details');
        }

        return $user->hasPermission('create bank details');
    }

    public function update(User $user, BankDetail $bankDetail): bool
    {
        return $bankDetail->branch->canBeAccessedBy($user) && $user->hasPermission('update bank details');
    }

    public function delete(User $user, BankDetail $bankDetail): bool
    {
        return $bankDetail->branch->canBeAccessedBy($user) && $user->hasPermission('delete bank details');
    }
}
