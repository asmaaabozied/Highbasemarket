<?php

namespace App\Services\Commission;

use App\Enum\CommissionLedgerStatusEnum;
use App\Http\Filters\AccountReportFilters;
use App\Models\Account;
use App\Models\Branch;
use App\Models\OrderLine;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;

class AccountReportService
{
    /**
     * Get paginated account summaries with commission stats.
     */
    public function getAccountSummary(AccountReportFilters $query, int $perPage = 15): LengthAwarePaginator
    {
        $paginator = $query
            ->execute(fn (QueryBuilder $query) => $query->with(['branches.orders.lines.commissionLedger']))
            ->paginate($perPage);

        $paginator->getCollection()->transform(function (Account $account): array {

            $lines = $account->branches
                ->flatMap(fn (Branch $branch) => $branch->stocks
                    ->flatMap->lines
                    ->filter(fn (OrderLine $line) => $line->delivered_at)
                );

            $totalUsd = $lines->sum('commission_amount_usd');
            $paidUsd  = $lines->sum(fn (OrderLine $l) => $l->commissionLedger?->paid_amount_usd ?? 0);
            $orders   = $lines
                ->pluck('order')
                ->filter()
                ->unique('id')
                ->count();

            $lastPayment = $lines
                ->filter(fn (OrderLine $l) => $l->commissionLedger?->paid_at)
                ->max(fn (OrderLine $l) => $l->commissionLedger?->paid_at);

            return [
                'id'                     => $account->id,
                'name'                   => $account->name,
                'total_commission_usd'   => round($totalUsd, 2),
                'total_orders'           => $orders,
                'paid_commission_usd'    => round($paidUsd, 2),
                'pending_commission_usd' => round($totalUsd - $paidUsd, 2),
                'last_payment_at'        => $lastPayment,
                'status'                 => $this->getCommissionStatus($totalUsd, $paidUsd),
            ];
        });

        return $paginator;
    }

    private function getCommissionStatus(float $total, float $paid): CommissionLedgerStatusEnum
    {
        return match (true) {
            $total == 0     => CommissionLedgerStatusEnum::NA,
            $paid >= $total => CommissionLedgerStatusEnum::PAID,
            $paid > 0       => CommissionLedgerStatusEnum::PARTIAL_PAID,
            default         => CommissionLedgerStatusEnum::UNPAID,
        };
    }
}
