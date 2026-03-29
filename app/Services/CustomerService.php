<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\CustomerVendor;
use App\Models\OrderLine;
use Exception;

class CustomerService
{
    public function getAvailableCredit(Branch $seller, CustomerVendor $pivot): float|int
    {
        try {
            $totalCredit = $pivot->config['credit_settings'];
            $totalCredit = $totalCredit['maximum_credit_limit'] ?? 0;

            $usedCredit = OrderLine::query()
                ->whereHas('order', function ($query) use ($pivot): void {
                    $query->where('branch_id', $pivot->customer_id)
                        ->where('payment_method', 'credit');
                })
                ->whereNotIn('status', ['rejected', 'cancelled'])
                ->whereHas('product', function ($query): void {
                    $query->where('branch_id', currentBranch()->id);
                })
                ->whereNull('paid_at')
                ->sum('total');

            return $totalCredit - $usedCredit;
        } catch (Exception) {
            return 0;
        }
    }
}
