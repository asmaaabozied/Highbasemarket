<?php

namespace App\Services;

use App\Interfaces\AssignableInterface;
use App\Models\Branch;
use App\Models\CustomerVendor;
use App\Models\Employee;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class AssigningService
{
    public static function getEmployees(Branch $branch, array $permissions): Collection
    {
        $account = $branch->account;

        return Employee::query()
            ->where('account_id', $account->id)
            ->where(function ($q) use ($permissions): void {
                $q->where('job_title', 'administrator')
                    ->orWhereHas('user', function ($query) use ($permissions): void {
                        $query->whereHas('roles', function ($query) use ($permissions): void {
                            $query->whereHas('permissions', function ($query) use ($permissions): void {
                                $query->whereIn('name', $permissions);
                            });
                        })
                            ->orWhereHas('permissions', function ($query) use ($permissions): void {
                                $query->whereIn('name', $permissions);
                            });
                    });
            })
            ->where(function ($query) use ($branch): void {
                $query->whereHas('branches', function ($query) use ($branch): void {
                    $query->where('branches.id', $branch->id);
                })
                    ->orWhereDoesntHave('branches');
            })
            ->with('user')
            ->get()->append(['assignee_type']);

    }

    // todo:: this should be changed in future when adding tagging system
    public static function getAssignees(AssignableInterface $assignable): Collection|\Illuminate\Support\Collection
    {
        if (auth()->user()->isAccountUser()) {
            return $assignable->assigns()
                ->with('assignee.user')
                ->whereHas('assignee', function ($query): void {
                    $query->where('account_id', auth()->user()->getAccount()->id);
                })
                ->get();
        }

        return $assignable->assigns()
            ->with('assignee.user')
            ->get();
    }

    public static function AutoAssignOrder(Order $order): void
    {
        $order->lines->unique('product.branch_id')->each(function ($line) use ($order): void {
            if ($assignee_id = self::getAutoOrderAssignee($line->product->branch, $order->branch)) {
                $order->assigns()
                    ->create([
                        'assignee_id'   => $assignee_id,
                        'assignee_type' => Employee::class,
                    ]);
            }
        });
    }

    private static function getAutoOrderAssignee(Branch $branch, Branch $buyer): ?int
    {
        $customer_vendor = CustomerVendor::where('vendor_id', $branch->id)
            ->where('customer_id', $buyer->id)
            ->first();

        return $customer_vendor?->assign_orders_to;
    }
}
