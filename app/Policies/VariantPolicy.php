<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Variant;
use Illuminate\Auth\Access\HandlesAuthorization;

class VariantPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool {}

    public function view(User $user, Variant $variant): bool {}

    public function create(User $user): bool {}

    public function update(User $user, Variant $variant): bool {}

    public function delete(User $user, Variant $variant): bool {}

    public function restore(User $user, Variant $variant): bool {}

    public function forceDelete(User $user, Variant $variant): bool {}
}
