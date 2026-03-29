<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Branch;

class EmployeeAccountServices
{
    public function getEmployeeBranchesByAccountId(): \Illuminate\Database\Eloquent\Collection|array
    {
        return Account::query()
            ->select('branches.*')
            ->where('accounts.id', auth()->user()?->userable->account_id)
            ->join('branches', 'branches.account_id', 'accounts.id')
            ->get();
    }

    public function getEmployeeCurrentBranchesByAccountId(): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder|null
    {

        return Account::query()
            ->select('branches.*')
            ->where('accounts.id', auth()->user()?->userable->account_id)
            ->join('branches', 'branches.account_id', 'accounts.id')
            ->first();
    }

    public function getEmployeeCurrentBranch(?int $accountId = null): Branch
    {
        return currentBranch();
    }

    /**
     * @throws \Exception
     */
    public function getUserIdsByBranchId(int $branchId): \Illuminate\Support\Collection
    {
        $branch = Branch::query()
            ->with('account.employees')
            ->find($branchId);

        if (! $branch->account) {
            throw new \Exception('There is no account available');
        }

        return $branch->account->employees->pluck('user.id');
    }

    public function getBranchUsersByAccountOrPermissions(Account $account, $permissions = [], $receiverBranchId = null): \Illuminate\Support\Collection
    {
        return $account->employees()
            ->where('job_title', 'administrator')
            ->orWhere(function ($where) use ($permissions, $receiverBranchId): void {
                $where->where(function ($qury) use ($receiverBranchId): void {
                    $qury->whereHas('branches', function ($qxry) use ($receiverBranchId): void {
                        $qxry->where('branches.id', $receiverBranchId);
                    })->orWhereDoesntHave('branches');
                })->whereHas('user', function ($userQuery) use ($permissions): void {
                    $userQuery->whereHas('roles.permissions', function ($permissionQuery) use ($permissions): void {
                        $permissionQuery->whereIn('name', $permissions);
                    });
                });
            })

            ->with('user')
            ->get();
    }
}
