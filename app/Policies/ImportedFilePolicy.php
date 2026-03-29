<?php

namespace App\Policies;

use App\Models\ImportedFile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ImportedFilePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool {}

    public function view(User $user, ImportedFile $importedFile): bool {}

    public function create(User $user): bool {}

    public function update(User $user, ImportedFile $importedFile): bool {}

    public function delete(User $user, ImportedFile $importedFile): bool {}

    public function restore(User $user, ImportedFile $importedFile): bool {}

    public function forceDelete(User $user, ImportedFile $importedFile): bool {}
}
