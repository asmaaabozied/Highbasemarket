<?php

namespace App\Services\Commission;

use App\Enum\CommissionLedgerStatusEnum;
use App\Http\Filters\BranchesFilter;
use App\Http\Filters\OrdersFilter;
use App\Models\Branch;
use App\Models\CommissionLedger;
use App\Models\CommissionOverride;
use App\Models\Order;
use App\Models\OrderLine;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

class CommissionReportService
{
    public function getBranchesByAccount(BranchesFilter $filters): LengthAwarePaginator
    {

        $branches   = $filters->paginate(10);
        $collection = $branches->getCollection()
            ->transform(function (Branch $branch): ?array {
                $lines = OrderLine::query()
                    ->whereNotNull('delivered_at')
                    ->whereHas('product', fn ($q) => $q->where('branch_id', $branch->id))
                    ->with(['commissionLedger'])
                    ->get();

                if ($lines->isEmpty()) {
                    return null;
                }

                $totalUsd   = $lines->sum('commission_amount_usd');
                $paidUsd    = $lines->sum(fn ($line) => $line->commissionLedger?->paid_amount_usd ?? 0);
                $totalLocal = $lines->sum('commission_amount_local_currency');
                $paidLocal  = $lines->sum(fn ($line) => $line->commissionLedger?->paid_amount_local_currency ?? 0);

                $remainingUsd   = $totalUsd - $paidUsd;
                $remainingLocal = $totalLocal - $paidLocal;

                $oldestUnpaid = $lines
                    ->filter(fn ($line
                    ): bool => ($line->commissionLedger?->paid_amount_usd ?? 0) < $line->commission_amount_usd)
                    ->min(fn ($line) => $line->delivered_at?->timestamp);

                $status = $this->getCommissionStatus(total: $totalUsd, paid: $paidUsd);

                $isOverdue = $oldestUnpaid && now()->subDays(7)->timestamp > $oldestUnpaid;

                $lastPayment = $lines
                    ->filter(fn ($line) => $line->commissionLedger?->paid_at)
                    ->max(fn ($line) => $line->commissionLedger->paid_at);

                $totalOrders = $lines
                    ->pluck('order')
                    ->filter()
                    ->unique('id')
                    ->count();

                return [
                    'id'                         => $branch->id,
                    'name'                       => $branch->name,
                    'last_payment'               => $lastPayment ?? null,
                    'total_orders'               => $totalOrders,
                    'total_commission_local'     => round($totalLocal, 2),
                    'total_commission_usd'       => round($totalUsd, 2),
                    'paid_commission_usd'        => round($paidUsd, 2),
                    'remaining_commission_usd'   => round($remainingUsd, 2),
                    'remaining_commission_local' => round($remainingLocal, 2),
                    'status'                     => $status,
                    'is_overdue'                 => $isOverdue,
                ];
            })
            ->filter()
            ->sortByDesc('is_overdue')
            ->values();
        $branches->setCollection($collection);

        return $branches;
    }

    public function getCommissionStatus(float $total, float $paid): CommissionLedgerStatusEnum
    {
        return match (true) {
            $total == 0     => CommissionLedgerStatusEnum::NA,
            $paid >= $total => CommissionLedgerStatusEnum::PAID,
            $paid > 0       => CommissionLedgerStatusEnum::PARTIAL_PAID,
            default         => CommissionLedgerStatusEnum::UNPAID,
        };
    }

    /**
     * Get order-wise summary for a branch
     */
    public function getOrdersByBranch(Branch $branch, OrdersFilter $filters): LengthAwarePaginator
    {
        return $filters->execute(function (QueryBuilder $query) use ($branch): void {
            $query->whereHas('lines.product', fn ($q) => $q->where('branch_id', $branch->id))
                ->whereHas('lines', fn ($q) => $q->whereNotNull('delivered_at'))
                ->with(['lines.commissionLedger', 'lines.product'])
                ->orderByDesc('created_at');
        })->paginate(10);

    }

    /**
     * Transform order for item-level view
     */
    public function getOrderDetail(OrdersFilter $filters): array
    {
        $order = $filters->execute(function (QueryBuilder $query): void {
            $query->with([
                'branch:id,name',
                'lines' => fn ($q) => $q->with([
                    'product.product:id,name',
                    'variant:id,name',
                    'commissionLedger',
                ]),
            ]);
        })->first();

        if (! $order) {
            return [];
        }
        $lines = $order->lines->map(function ($line): array {
            $ledger     = $line->commissionLedger;
            $paidUsd    = $ledger?->paid_amount_usd ?? 0;
            $totalUsd   = $line->commission_amount_usd;
            $totalLocal = $line->commission_amount_local_currency;

            // Convert paid USD into local currency using the line’s exchange rate
            $exchangeRate         = $line->exchange_rate_to_usd ?? 1;
            $paidLocal            = floor($paidUsd * $exchangeRate * 100) / 100;
            $commission_due_local = round($totalLocal - $paidLocal, 2);

            return [
                'id'                             => $line->id,
                'image'                          => $line->product->image,
                'product_name'                   => $line->variant?->name,
                'quantity'                       => $line->quantity,
                'total'                          => $line->total,
                'price'                          => $line->price,
                'commission_percentage'          => $line->commission_percentage.' %',
                'commission_amount_local'        => $totalLocal,
                'commission_local_currency_code' => $line->commission_local_currency_code,
                'commission_amount_usd'          => $totalUsd,
                'commission_paid_usd'            => $paidUsd,
                'commission_due_usd'             => $totalUsd - $paidUsd,
                'commission_paid_local'          => $paidLocal,
                'commission_due_local'           => $commission_due_local,
                'commission_status'              => $ledger?->status ?? CommissionLedgerStatusEnum::UNPAID,
                'payable_at'                     => $ledger?->payable_at,
                'paid_at'                        => $ledger?->paid_at,
            ];
        });

        $total                   = $lines->sum('commission_amount_usd');
        $paid                    = $lines->sum('commission_paid_usd');
        $status                  = $this->getCommissionStatus(total: $total, paid: $paid);
        $avgCommission           = $lines->isNotEmpty() ? round($lines->avg('commission_amount_usd'), 2) : 0;
        $commissionableCount     = $lines->filter(fn ($line): bool => $line['commission_percentage'] > 0)->count();
        $commission_amount_local = $lines->sum('commission_amount_local');
        $commission_due_local    = $lines->sum('commission_due_local');

        return [
            'id'                         => $order->id,
            'number'                     => $order->number,
            'branch_name'                => $order->branch->name,
            'created_at'                 => $order->created_at,
            'lines'                      => $lines,
            'total_commission_usd'       => number_format(round($lines->sum('commission_amount_usd'), 2), 2),
            'total_commission_local'     => number_format(round($commission_amount_local, 2), 2),
            'paid_commission_usd'        => round($lines->sum('commission_paid_usd'), 2),
            'total_commission_due_local' => number_format(round($commission_due_local, 2), 2),
            'status'                     => $status,
            'order_total'                => $order->total,
            'avg_commission_usd'         => $avgCommission,
            'commissionable_items'       => $commissionableCount,
            'local_currency_code'        => $lines->first()['commission_local_currency_code'],
        ];
    }

    /**
     * @throws Throwable
     */
    public function overrideCommission(int $orderId, float $usd, string $reason, int $adminId): void
    {
        DB::transaction(function () use ($orderId, $usd, $reason, $adminId): void {
            $order = Order::with('lines')->lockForUpdate()->findOrFail($orderId);

            // Prevent override if any commission payment has already been made for this order
            $hasPaidLedger = CommissionLedger::whereIn('order_line_id', $order->lines->pluck('id'))
                ->whereNotNull('paid_at')
                ->exists();

            if ($hasPaidLedger) {
                throw new Exception(__('Cannot override commission: payment has already been made for one or more items.'));
            }

            foreach ($order->lines as $line) {
                $exchangeRate = $line->exchange_rate_to_usd;
                $local        = round($usd * $exchangeRate, 2);

                // Keep old values for override record
                $oldUsd   = $line->commission_amount_usd;
                $oldLocal = $line->commission_amount_local_currency;

                // Update order line
                $line->update([
                    'commission_amount_usd'            => $usd,
                    'commission_amount_local_currency' => $local,
                ]);

                // Update ledger
                CommissionLedger::where('order_line_id', $line->id)
                    ->update([
                        'amount_usd' => $usd,
                    ]);

                // Record override
                CommissionOverride::create([
                    'order_id'                => $order->id,
                    'order_line_id'           => $line->id,
                    'override_by_id'          => $adminId,
                    'commission_usd_before'   => $oldUsd,
                    'commission_usd_after'    => $usd,
                    'commission_local_before' => $oldLocal,
                    'commission_local_after'  => $local,
                    'reason'                  => $reason,
                ]);
            }
        });
    }

    public function calculateOutstandingCommissionUsd(OrdersFilter $filters): float
    {
        $branch = currentBranch();
        $orders = $filters->forSeller($branch)
            ->execute(function (QueryBuilder $builder): void {
                $builder->whereHas('lines', function ($query): void {
                    $query->whereNotNull('delivered_at');
                })->with([
                    'lines' => fn ($q) => $q->with([
                        'product.product:id,name',
                        'variant:id,name',
                        'commissionLedger',
                    ]),
                ])
                    ->orderByDesc('created_at');
            })->get();

        $totalOutstanding = $orders->pluck('lines')
            ->flatten()
            ->sum(function ($line): int|float {
                $totalUsd = $line->commission_amount_usd ?? 0;
                $paidUsd  = $line->commissionLedger?->paid_amount_usd ?? 0;

                return $totalUsd - $paidUsd;
            });

        return round($totalOutstanding, 2);
    }
}
