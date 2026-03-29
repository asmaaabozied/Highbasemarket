<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if ($user->hasPermission('view all orders')) {
            return true;
        }

        if ($user->hasPermission('view order')) {
            return true;
        }

        return $user->hasPermission('create instant order');
    }

    public function viewPurchases(User $user): bool
    {
        return $user->hasPermission('view all purchases');
    }

    public function view(User $user, Order $order): bool
    {
        $hasPermission = $user->hasPermission('create instant order')
            || $user->hasPermission('view purchase')
            || $user->hasPermission('view order');

        $hasAccess = $user->isAdmin() || $order->canBeAccessedBy($user->getAccount());

        return $hasPermission && $hasAccess;

    }

    public function create(User $user): bool
    {
        // Disabled account check for purchase orders as per Mr.Abdulrahman request 27-oct-2025
        //        if (currentBranch()->account->status !== 'active') {
        //            abort(403, 'You cannot create a purchase order for an inactive account');
        //        }

        if (currentBranch()->status != 'active') {
            abort(403, 'You cannot create a purchase order for an inactive branch');
        }

        //        if (! currentBranch()->cr) {
        //            abort(403, 'You cannot create a purchase order for a branch without a commercial registration');
        //        }

        if (! currentBranch()->address) {
            $this->deny('You cannot create a purchase order for a branch without an address');
        }

        return $user->hasPermission('create purchase');
    }

    protected function deny(string $message): void
    {
        abort(403, $message);
    }

    public function update(User $user, Order $order): bool
    {
        if ($order->branch_id === currentBranch()->id) {
            return $user->hasPermission('update purchase')
                && ($order->canBeAccessedBy($user->getAccount()) || $user->isAdmin());
        }

        return $user->hasPermission('update order')
            && ($order->canBeAccessedBy($user->getAccount()) || $user->isAdmin());
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->hasPermission('cancel purchase')
            && ($order->canBeAccessedBy($user->getAccount()) || $user->isAdmin());
    }

    public function instantOrders(User $user, Branch $branch): bool
    {
        if ($user->getAccount()->id !== $branch->account->id) {
            return false;
        }

        if ($user->isAdministrator()) {
            return true;
        }

        return $user->hasPermission('create instant order');
    }

    public function storeInstantOrder(
        User $user,
        Branch $currentBranch,
        ?Branch $customer = null,
        ?string $status = null
    ): bool {
        $this->createInstantOrder($user, $currentBranch, $status);

        if (! $customer instanceof \App\Models\Branch) {
            return true;
        }

        if ($customer->account?->status !== 'active') {
            $this->deny('Cannot create an order for an inactive account');
        }

        if (! $customer->address) {
            $this->deny('Cannot create an order for a branch without an address');
        }

        return true;
    }

    public function createInstantOrder(User $user, Branch $currentBranch, ?string $status = null): bool
    {
        return $this->canAccessInstantOrder($user, $currentBranch, $status);
    }

    protected function canAccessInstantOrder(User $user, Branch $branch, ?string $status = null): ?bool
    {
        if ($user->getAccount()->id !== $branch->account_id) {
            $this->deny('You do not belong to this account');
        }

        if ($user->isAdministrator()) {
            return true;
        }

        if ((! $user->hasPermission('create instant order'))) {
            $this->deny('You do not have permission to create instant orders');
        }

        if ($status === 'shipped' && ! $user->hasPermission('ship instant order') && ! $user->hasPermission('ship order')) {
            $this->deny('You do not have ship order permission');
        }

        if ($status === 'delivered' && ! $user->hasPermission('deliver instant order') && ! $user->hasPermission('deliver order')) {
            $this->deny('You do not have deliver order permission');
        }

        return true;
    }
}
