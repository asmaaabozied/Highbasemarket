<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view all members');
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->hasPermission('view member')
            && $employee->canBeAccessedBy($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create member');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasPermission('update member')
            && $employee->canBeAccessedBy($user);
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasPermission('delete member')
            && $employee->canBeAccessedBy($user);
    }

    public function restore(User $user, Employee $employee): bool
    {
        return $user->hasPermission('restore employee');
    }

    public function forceDelete(User $user, Employee $employee): bool
    {
        return $user->hasPermission('forceDelete employee');
    }

    public function updateStatus(User $user, Employee $employee): bool
    {
        return $user->hasPermission('update status');
    }
}
