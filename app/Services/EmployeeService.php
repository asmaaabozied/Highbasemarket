<?php

namespace App\Services;

use App\Http\Filters\BranchesFilter;
use App\Http\Filters\VendorCustomersFilter;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;

class EmployeeService
{
    public function __construct(private readonly ?Employee $employee = null) {}

    public function getEmployeesByBranchId($branchId): LengthAwarePaginator
    {
        return Branch::query()
            ->select('users.*', 'employees.status')
            ->join('accounts', 'accounts.id', 'branches.account_id')
            ->join('employees', function ($join): void {
                $join->on('accounts.id', 'employees.account_id');
                $join->where('employees.job_title', 'administrator');
            })
            ->join('users', 'users.userable_id', 'employees.id')
            ->where('branches.id', $branchId)
            ->paginate();
    }

    public function invitedCustomers(VendorCustomersFilter $filter): Paginator
    {
        return $filter->forVendorAccount($this->employee->account)
            ->forEmployee($this->employee)
            ->paginate();
    }

    public function invitedVendors(VendorCustomersFilter $filter): Paginator
    {
        return $filter->forCustomerAccount($this->employee->account)
            ->forEmployee($this->employee)
            ->paginate();
    }

    public function CustomersCount(VendorCustomersFilter $filter): int
    {
        return $filter->forVendorAccount($this->employee->account)
            ->forEmployee($this->employee)
            ->count();
    }

    public function VendorsCount(VendorCustomersFilter $filter): int
    {
        return $filter->forCustomerAccount($this->employee->account)
            ->forEmployee($this->employee)
            ->count();
    }

    public function assignedBranches(BranchesFilter $filter): Paginator
    {
        $branches = $filter->execute(function ($query): void {
            $query->select(['id', 'slug', 'account_id', 'name', 'status'])
                ->withWhereHas('employees', fn ($query) => $query->where('employee_id', $this->employee->id));
        })
            ->forAccount($this->employee->account)
            ->paginate();

        $branches->transform(function ($branch) {
            $branch->joining_date = $branch->employees->first();

            return $branch;
        });

        return $branches;
    }

    public function setMaxDiscountPercentage(Employee $employee, float $value): Employee
    {
        $employee->update(['max_discount_percentage' => $value]);

        return $employee;
    }
}
