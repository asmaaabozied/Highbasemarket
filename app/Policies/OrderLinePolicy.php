<?php

namespace App\Policies;

use App\Enum\OrderTypeEnum;
use App\Models\OrderLine;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderLinePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool {}

    public function view(User $user, OrderLine $orderLine): bool {}

    public function create(User $user): bool {}

    public function update(User $user, OrderLine $orderLine): bool {}

    public function delete(User $user, OrderLine $orderLine): bool {}

    public function restore(User $user, OrderLine $orderLine): bool {}

    public function forceDelete(User $user, OrderLine $orderLine): bool {}

    public function approve(User $user, OrderLine $orderLine): bool
    {
        if ($user->isAdmin()) {
            return $user->hasPermission('approve order');
        }

        return $user->hasPermission('approve order') &&
            $orderLine->product->branch_id === currentBranch()->id && $orderLine->status === 'pending';
    }

    public function ship(User $user, OrderLine $orderLine): bool
    {
        if ($user->isAdmin()) {
            return $user->hasPermission('ship order');
        }

        $permission = $user->hasPermission('ship order');

        if ($orderLine->order->order_type->value === OrderTypeEnum::INSTANT_ORDER->value) {
            $permission = $user->hasPermission('ship order') ||
                $user->hasPermission('ship instant order');
        }

        return $permission &&
            $orderLine->product->branch_id === currentBranch()->id && $orderLine->status === 'approved';
    }

    public function reject(User $user, OrderLine $orderLine): bool
    {
        if ($user->isAdmin()) {
            return $user->hasPermission('reject order');
        }

        return $user->hasPermission('reject order') &&
            $orderLine->product->branch_id === currentBranch()->id && $orderLine->status === 'pending';
    }

    public function deliver(User $user, OrderLine $orderLine): bool
    {
        if ($user->isAdmin()) {
            return $user->hasPermission('deliver order');
        }

        $permission = $user->hasPermission('deliver order');

        if ($orderLine->order->order_type->value === OrderTypeEnum::INSTANT_ORDER->value) {
            $permission = $user->hasPermission('deliver order') ||
                $user->hasPermission('deliver instant order');
        }

        return $permission && $orderLine->product->branch_id === currentBranch()->id && $orderLine->status === 'shipped';
    }
}
