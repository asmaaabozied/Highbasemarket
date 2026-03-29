<?php

namespace App\Services\Commission;

use App\Models\OrderLine;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GlobalStatsService
{
    public function getGlobalSummary(array $filters = []): array
    {
        $stats = OrderLine::query()
            ->selectRaw('
            SUM(order_lines.commission_amount_usd) as total_usd,
            SUM(COALESCE(cl.paid_amount_usd, 0)) as paid_usd,
            COUNT(DISTINCT orders.id) as orders_count
        ')
            ->leftJoin('commission_ledgers as cl', 'cl.order_line_id', '=', 'order_lines.id')
            ->join('orders', 'orders.id', '=', 'order_lines.order_id')
            ->whereNotNull('order_lines.delivered_at')
            ->first();

        $totalUsd        = $stats->total_usd ?? 0;
        $paidUsd         = $stats->paid_usd ?? 0;
        $ordersCount     = $stats->orders_count ?? 0;
        $avgCommPerOrder = $ordersCount > 0 ? round($totalUsd / $ordersCount, 2) : 0;

        return [
            'total_commission_usd'     => round($totalUsd, 2),
            'pending_commission_usd'   => round($totalUsd - $paidUsd, 2),
            'total_orders'             => $ordersCount,
            'avg_commission_per_order' => $avgCommPerOrder,
        ];
    }

    public function getTopAccounts($limit = 5): Collection
    {
        return DB::table('order_lines')
            ->select(
                'accounts.id',
                'accounts.name',
                DB::raw('SUM(cl.paid_amount_usd) as total_commission_usd')
            )
            ->join('commission_ledgers as cl', 'cl.order_line_id', '=', 'order_lines.id')
            ->join('stocks', 'stocks.id', '=', 'order_lines.product_id')
            ->join('branches', 'branches.id', '=', 'stocks.branch_id') // seller branch
            ->join('accounts', 'accounts.id', '=', 'branches.account_id')
            ->whereNotNull('order_lines.delivered_at')
            ->whereNotNull('cl.paid_at')
            ->where('cl.paid_at', '>=', now()->subMonths(12))
            ->groupBy('accounts.id', 'accounts.name')
            ->orderByDesc('total_commission_usd')
            ->limit($limit)
            ->get();
    }

    public function getCommissionTrend(string $periodType, CarbonInterface $from, CarbonInterface $to): array
    {
        $trend = OrderLine::query()
            ->selectRaw($this->periodFormat($periodType).' as period, SUM(commission_amount_usd) as total_usd')
            ->whereBetween('delivered_at', [$from, $to])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'dates'  => $trend->pluck('period'),
            'labels' => $trend->map(fn ($item): string => $this->formatLabel($item->period, $periodType)),
            'data'   => $trend->pluck('total_usd'),
        ];
    }

    private function periodFormat(string $type): string
    {
        return match ($type) {
            'day'     => 'DATE(delivered_at)',
            'week'    => 'YEARWEEK(delivered_at)',
            'month'   => 'DATE_FORMAT(delivered_at, "%Y-%m")',
            'quarter' => 'CONCAT(YEAR(delivered_at), "-Q", QUARTER(delivered_at))',
            'year'    => 'YEAR(delivered_at)',
        };
    }

    private function formatLabel(string $period, string $type): string
    {
        return match ($type) {
            'month' => Carbon::createFromFormat('Y-m', $period)->format('M'),
            'day'   => Carbon::parse($period)->format('d M'),
            'week'  => 'W'.substr($period, 4), // YEARWEEK returns like 202506
            default => $period
        };
    }
}
