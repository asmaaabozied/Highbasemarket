<?php

namespace App\Policies;

use App\Models\CustomerVendor;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerVendorsPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view all customers');
    }

    public function view(User $user, CustomerVendor $customerVendors): bool
    {
        return $user->hasPermission('view customer');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create customer');
    }

    public function update(User $user, CustomerVendor $customerVendors): bool
    {
        return $user->hasPermission('update customer');
    }

    public function delete(User $user, CustomerVendor $customerVendors): bool
    {
        return $user->hasPermission('delete customer');
    }

    public function restore(User $user, CustomerVendor $customerVendors): bool
    {
        return $user->hasPermission('restore customer');
    }

    public function forceDelete(User $user, CustomerVendor $customerVendors): bool
    {
        return $user->hasPermission('forceDelete customer');
    }
}
