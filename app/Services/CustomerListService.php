<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\CustomerVendor;

class CustomerListService
{
    public function getVendorCustomerList(): ?Branch
    {
        return currentBranch()?->load([
            'customers:id,slug,name,address,email,phone',
            'vendors:id,slug,name,address,email,phone',
        ]);
    }

    public function getCustomerListByListOfBranchId(array $branch)
    {
        return CustomerVendor::query()
            ->with('vendor')
            ->whereIn('customer_id', $branch)
            ->whereIn('vendor_id', $branch)
            ->first();
    }

    public function checkCustomerList($customerId, $vendorId = null): bool
    {
        if (! $vendorId) {
            $vendorId = currentBranch()->id;
        }

        return CustomerVendor::query()
            ->where('customer_id', $customerId)
            ->where('vendor_id', $vendorId)
            ->exists();
    }

    public function getInviteBranchFormList($branchId, $vendor = null)
    {

        if (! $vendor) {
            $vendor = currentBranch()->id;
        }

        return CustomerVendor::where(function ($where) use ($vendor, $branchId): void {
            $where->where('vendor_id', $vendor)
                ->where('customer_id', $branchId);
        })->orWhere(function ($where) use ($vendor, $branchId): void {
            $where->where('customer_id', $vendor)
                ->where('vendor_id', $branchId);
        });
    }

    public static function addToCustomerList(Branch $vendor, Branch $customer): void
    {
        if (! $customer->isCustomerOf($vendor)) {
            $vendor->customers()->attach($customer->id);
        }
    }
}
