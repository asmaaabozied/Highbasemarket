<?php

namespace App\Policies;

use App\Models\Stock;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view all stocks');
    }

    public function view(User $user, Stock $stock): bool
    {
        return $user->hasPermission('view stock');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create stock');
    }

    public function update(User $user, Stock $stock): bool
    {
        if ($user->isAdmin()) {
            return $user->hasPermission('update stock');
        }

        return $user->hasPermission('update stock')
            && $stock->canBeAccessedBy(currentBranch()->account);
    }

    public function lockPrice(User $user): bool
    {
        return $user->hasPermission('update stock');
    }

    public function delete(User $user, Stock $stock): bool
    {
        if ($user->isAccountUser()) {
            return $user->hasPermission('delete stock') && $stock->branch_id == currentBranch()->id;
        }

        return $user->hasPermission('delete stock');
    }

    public function restore(User $user, Stock $stock): bool
    {
        return $user->hasPermission('restore stock');
    }

    public function forceDelete(User $user, Stock $stock): bool
    {
        return $user->hasPermission('forceDelete stock');
    }
}
