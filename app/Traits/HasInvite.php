<?php

namespace App\Traits;

use App\Enum\InviteType;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

trait HasInvite
{
    public function inviteSession($branchId, $local = false): void
    {
        if (request()->has('type')
            && ! collect(Session::get('invitations', []))->contains(fn (array $employ): bool => $employ['branch_id'] === $branchId && $employ['type'] === request('type'))
        ) {
            Session::push('invitations', [
                'employee_id' => request('employee'),
                'branch_id'   => $branchId,
                'type'        => request('type'),
            ]);
        }
    }

    public function invitation($branchId): Collection
    {
        if (Session::has('invitations')) {
            $invitation = (object) collect(Session::get('invitations'))
                ->where('type', request('type'))
                ->where('branch_id', $branchId)
                ->last();

            if (request()->has('type')) {
                return collect([
                    'branch' => Branch::query()
                        ->select('id', 'name', 'phone', 'email')
                        ->where('id', $invitation?->branch_id)->first(),
                    'employee' => $this->getEmployee($invitation, $branchId),
                ]);
            }
        }

        return collect();
    }

    public function isVendorInvitation(int $branchId): bool
    {
        if (! Session::has('invitations')) {
            return false;
        }

        $invitation = collect(Session::get('invitations'))
            ->where('type', request('type'))
            ->where('branch_id', $branchId)
            ->last();

        return $invitation && $invitation['type'] === InviteType::VENDOR->value;
    }

    private function getEmployee($invitation, $branch_id): ?Employee
    {
        if ($invitation?->employee_id) {
            return Employee::query()
                ->select('id', 'job_position', 'linkedin_profile')
                ->with('user:id,userable_id,first_name,last_name,email,phone,profile_photo_path')
                ->where('id', $invitation?->employee_id)->first();
        }

        $branch = Branch::query()->select('id', 'config')->find($branch_id);

        return $branch->getDefaultEmployee();
    }
}
