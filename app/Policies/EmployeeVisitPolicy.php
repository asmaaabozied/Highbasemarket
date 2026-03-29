<?php

namespace App\Policies;

use App\EmployeeJobEnum;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeVisit;
use App\Models\User;

class EmployeeVisitPolicy
{
    public function viewAll(User $user): bool
    {
        if ($user->hasPermission('view all employee visits')) {
            return true;
        }

        return $user->hasPermission('view employee visit');
    }

    public function view(User $user, EmployeeVisit $employeeVisit): bool
    {
        return $user->hasPermission('view employee visit');
    }

    public function create(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return $user->hasPermission('create employee visit');
    }

    public function update(User $user, EmployeeVisit $employeeVisit, ?Branch $branch = null): bool
    {
        // For highbase branch: allow any highbase employee to confirm
        if ($employeeVisit->isHighbaseBranchVisit()) {
            $highbaseBranchId = Branch::getHighbaseBranchId();

            if ($highbaseBranchId && $branch?->id === $highbaseBranchId) {
                // Check if user's employee belongs to highbase branch
                $employee = $user->userable;

                if ($employee && $employee->branches()->where('branches.id', $highbaseBranchId)->exists()) {
                    return true;
                }
            }
        }

        if ($employeeVisit->canAccess(user: $user, branch: $branch)) {
            return true;
        }

        return $user->hasPermission('update employee visit') &&
            $employeeVisit->employee_id === $user->userable_id;
    }

    public function delete(User $user, EmployeeVisit $employeeVisit, ?Branch $branch = null): bool
    {
        if ($employeeVisit->canAccess(user: $user, branch: $branch)) {
            return true;
        }

        return $user->hasPermission('delete employee visit') &&
            $employeeVisit->employee_id === $user->userable_id;
    }

    public function restore(User $user, EmployeeVisit $employeeVisit, ?Branch $branch = null): bool
    {
        return $employeeVisit->canAccess(user: $user, branch: $branch);
    }

    public function forceDelete(User $user, EmployeeVisit $employeeVisit, ?Branch $branch = null): bool
    {
        return $employeeVisit->canAccess(user: $user, branch: $branch);
    }

    public function viewTimeline(User $user): bool
    {
        return $user->hasPermission('view timeline');
    }

    public function removeFutureVisit(User $user, EmployeeVisit $employeeVisit, ?Branch $branch = null): bool
    {
        if ($employeeVisit->canAccess(user: $user, branch: $branch)) {
            return true;
        }

        return $user->hasPermission('remove future visit') &&
            $employeeVisit->employee_id == $user->userable_id;
    }

    public function postponedFutureVisit(User $user, EmployeeVisit $employeeVisit, ?Branch $branch = null): bool
    {
        $job           = strtolower((string) $user->userable->job_title);
        $canAccess     = $employeeVisit->canAccess(user: $user, branch: $branch);
        $hasPermission = $user->hasPermission('postponed future visit');

        // If user has access by relationship
        if ($canAccess) {
            return true;
        }

        // If user has permission, allow if he created or is assigned
        if ($hasPermission) {
            if ($employeeVisit->created_by === $user->id || $employeeVisit->employee_id === $user->userable_id) {
                return true;
            }

            // Manager can postpone any visit in his branch
            if ($job === EmployeeJobEnum::MANAGER->value) {
                return $employeeVisit->branch_id === $branch->id;
            }

            // Supervisor can postpone his own + other employees' visits from same branch,
            // but cannot postpone visits created by a manager
            if ($job === EmployeeJobEnum::SUPERVISOR->value) {
                return $employeeVisit->branch_id === $branch->id
                    && strtolower($employeeVisit->creator->job_title ?? '') != EmployeeJobEnum::MANAGER->value;
            }

            // Employee can only postpone his own visits
            if ($job === EmployeeJobEnum::EMPLOYEE->value) {
                return $employeeVisit->employee_id === $user->userable_id;
            }
        }

        // Default deny
        return false;
    }

    public function share(User $user, EmployeeVisit $employeeVisit): bool
    {
        // Admin can share any visit
        if ($user->isAdministrator()) {
            return true;
        }

        $job = strtolower($user->userable->job_title ?? '');

        // Manager or Supervisor can share visits they can access
        if (in_array($job, [
            EmployeeJobEnum::MANAGER->value,
            EmployeeJobEnum::SUPERVISOR->value,
        ])) {
            return $employeeVisit->canAccess(user: $user);
        }

        return false;
    }

    public function revokeShare(User $user, EmployeeVisit $employeeVisit): bool
    {
        // Admin can revoke any share
        if ($user->isAdministrator()) {
            return true;
        }

        $job = strtolower($user->userable->job_title ?? '');

        // Employee can revoke if they created the visit or are the assigned owner of the visit
        if ($job === EmployeeJobEnum::EMPLOYEE->value) {
            return $employeeVisit->created_by === $user->id
                || $employeeVisit->employee_id === $user->userable_id;
        }

        // Manager or Supervisor can revoke if they can access the visit
        if (in_array($job, [
            EmployeeJobEnum::MANAGER->value,
            EmployeeJobEnum::SUPERVISOR->value,
        ])) {
            return $employeeVisit->canAccess(user: $user);
        }

        return false;
    }

    public function rescheduleFutureVisit(User $user, EmployeeVisit $employeeVisit, ?Branch $branch = null): bool
    {
        if ($employeeVisit->canAccess(user: $user, branch: $branch)) {
            return true;
        }

        return $user->hasPermission('reschedule future visit') &&
            $employeeVisit->employee_id === $user->userable_id;
    }
}
