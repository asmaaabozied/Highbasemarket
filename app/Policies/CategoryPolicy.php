<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view all categories');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasPermission('view category');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create category');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasPermission('update category');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasPermission('delete category');
    }

    public function restore(User $user, Category $category): bool
    {
        return $user->hasPermission('restore category');
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return $user->hasPermission('forceDelete category');
    }
}
