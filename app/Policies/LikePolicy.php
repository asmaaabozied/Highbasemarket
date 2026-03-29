<?php

namespace App\Policies;

use App\Models\Like;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LikePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool {}

    public function view(User $user, Like $like): bool {}

    public function create(User $user): bool {}

    public function update(User $user, Like $like): bool {}

    public function delete(User $user, Like $like): bool {}

    public function restore(User $user, Like $like): bool {}

    public function forceDelete(User $user, Like $like): bool {}
}
