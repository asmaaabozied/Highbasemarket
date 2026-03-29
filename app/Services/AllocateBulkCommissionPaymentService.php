<?php

namespace App\Services;

use App\Enum\CommissionLedgerStatusEnum;
use App\Models\Branch;
use App\Models\CommissionLedger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class AllocateBulkCommissionPaymentService
{
    /**
     * @throws Throwable
     */
    public function allocate(Branch $seller, float $paymentAmount): void
    {
        DB::transaction(function () use ($seller, $paymentAmount): void {
            // Only get items that still owe money
            $ledgers   = $this->getPayableLedgers($seller);
            $remaining = $paymentAmount;

            foreach ($ledgers as $ledger) {
                if ($remaining <= 0) {
                    break;
                }
                // Due is still needed to avoid over-applying
                $due   = $ledger->amount_usd - $ledger->paid_amount_usd;
                $apply = min($due, $remaining);

                $ledger->paid_amount_usd += $apply;
                $remaining -= $apply;

                // Update status
                if (bccomp((string) $ledger->paid_amount_usd, (string) $ledger->amount_usd, 2) === 0) {
                    $ledger->status  = CommissionLedgerStatusEnum::PAID;
                    $ledger->paid_at = now();
                } else {
                    $ledger->status = CommissionLedgerStatusEnum::PARTIAL_PAID;
                }

                $ledger->save();
            }
        });
    }

    private function getPayableLedgers(Branch $seller): Collection
    {

        return CommissionLedger::query()
            ->with(['lineItem' => fn ($q) => $q->with('product')])
            ->whereHas('lineItem.product', fn ($q) => $q->where('branch_id', $seller->id))
            ->whereNotNull('payable_at') // delivered
            ->whereColumn('paid_amount_usd', '<', 'amount_usd') // not fully paid
            ->orderBy('payable_at') // oldest first
            ->get();
    }
}
