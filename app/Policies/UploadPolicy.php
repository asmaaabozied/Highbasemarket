<?php

namespace App\Policies;

use App\Models\Upload;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UploadPolicy
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
        return $user->hasPermission('view all uploads');
    }

    public function view(User $user, Upload $upload): bool
    {
        return $user->hasPermission('view upload') && $user->getAccount()->id === $upload->accout_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create upload');
    }

    public function update(User $user, Upload $upload): bool
    {
        return $user->hasPermission('update upload');
    }

    public function delete(User $user, Upload $upload): bool
    {
        return $user->hasPermission('delete upload') && $user->getAccount()->id === $upload->accout_id;
    }

    public function restore(User $user, Upload $upload): bool
    {
        return true;
    }

    public function forceDelete(User $user, Upload $upload): bool
    {
        return true;
    }
}
