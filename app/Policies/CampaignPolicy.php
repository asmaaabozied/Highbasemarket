<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CampaignPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool {}

    public function view(User $user, Campaign $campaign): bool {}

    public function create(User $user): bool {}

    public function update(User $user, Campaign $campaign): bool {}

    public function delete(User $user, Campaign $campaign): bool {}

    public function restore(User $user, Campaign $campaign): bool {}

    public function forceDelete(User $user, Campaign $campaign): bool {}
}
