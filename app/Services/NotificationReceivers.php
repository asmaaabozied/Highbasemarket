<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Support\Collection;

class NotificationReceivers
{
    private array|Collection $receivers = [];

    public function __construct(private readonly Account $account, private readonly ?Branch $branch = null, private readonly array $permissions = []) {}

    public static function make(Account $account, ?Branch $branch = null, array $permissions = []): self
    {
        return new static($account, $branch, $permissions);
    }

    public function get()
    {
        $this->receivers();
        $this->branchReceivers();
        $this->addAccountAdmin();

        return gettype($this->receivers) === 'array' ? collect($this->receivers)->pluck('user') : $this->receivers->pluck('user')->unique();
    }

    private function receivers(): void
    {
        $this->receivers = $this->account
            ->employees()
            ->whereHas('user', function ($query): void {
                $query->whereHas('roles', function ($query): void {
                    $query->whereHas('permissions', function ($query): void {
                        $query->whereIn('name', $this->permissions);
                    });
                });
            })
            ->with('user')
            ->get();
    }

    private function branchReceivers(): void
    {
        $this->receivers = $this->receivers
            ->map(function ($employee) {
                if (! $this->canAccessBranch($employee)) {
                    return null;
                }

                return $employee;
            });
    }

    private function canAccessBranch(Employee $employee): bool
    {
        if (! $this->restrictedBranch() && ! $employee->branch_id) {
            return true;
        }

        return $employee->branch->is($this->branch);
    }

    public function restrictedBranch(): bool
    {
        return false;
    }

    public function addAccountAdmin(): void
    {
        if (gettype($this->receivers) === 'array') {
            $this->receivers[] = $this->account->employees()
                ->where('job_title', 'administrator')
                ->with('user')->first();
        } else {
            $this->receivers->push(
                $this->account->employees()
                    ->where('job_title', 'administrator')
                    ->with('user')->first()
            );
        }
    }
}
