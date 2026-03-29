<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\ScheduleVisit;
use App\Models\User;

class ScheduleVisitPolicy
{
    public function viewAll(User $user): bool
    {
        if ($user->hasPermission('view all schedule visits')) {
            return true;
        }

        return $user->hasPermission('view schedule visit');
    }

    public function view(User $user, ScheduleVisit $visit, ?Branch $branch = null): bool
    {
        return $visit->canAccess(user: $user, branch: $branch) &&
            $user->hasPermission('view schedule visit') &&
            $visit->employee_id === $user->userable_id;
    }

    public function create(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return $user->hasPermission('create schedule visit');
    }

    public function update(User $user, ScheduleVisit $visit, ?Branch $branch = null): bool
    {
        // Check basic access and permission
        $hasAccess = $visit->canAccess(user: $user, branch: $branch) ||
            ($user->hasPermission('update schedule visit') && $visit->created_by === $user->id);

        if (! $hasAccess) {
            return false;
        }
        $targetUser = $visit->creator;

        $currentUser = auth()->user();

        if (! $currentUser->canActOn($targetUser)) {
            abort(403, 'You are not authorized to edit this visit.');
        }

        return true;
    }

    public function delete(User $user, ScheduleVisit $visit, ?Branch $branch = null): bool
    {
        if ($visit->canAccess(user: $user, branch: $branch)) {
            return true;
        }

        return $user->hasPermission('delete schedule visit');
    }
}
