<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view all products');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->hasPermission('view product');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create product');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasPermission('update product');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasPermission('delete product');
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->hasPermission('restore product');
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return $user->hasPermission('forceDelete product');
    }
}
