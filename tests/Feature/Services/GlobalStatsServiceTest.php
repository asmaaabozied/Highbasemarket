<?php

use App\Enum\CommissionLedgerStatusEnum;
use App\Events\OrderLineDelivered;
use App\Models\Account;
use App\Models\CommissionLedger;
use App\Services\Commission\GlobalStatsService;

use function Tests\Feature\createAccount;
use function Tests\Feature\Http\Controllers\Account\Order\createOrder;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\createMultiStocks;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\populateProducts;

beforeEach(fn () => $this->service = new GlobalStatsService);

// ———————————————————————————————————————
// HELPERS
// ———————————————————————————————————————

function updateLineToDelivered($order, $paidAmount = null, $deliveredAt = null, $paidAt = null)
{
    $deliveredAt = $deliveredAt ?? now()->subDay();
    $paidAt      = $paidAt ?? now()->subHour();

    // Use first line item from order (already has product, price, etc.)
    $line = $order->lines->first();
    expect($line)->not->toBeNull('Order has no line items');

    // Update to delivered
    $line->update([
        'status'       => 'delivered',
        'delivered_at' => $deliveredAt,
    ]);

    // Trigger event (if not automatic)
    event(new OrderLineDelivered($line));

    // Get or create commission ledger
    $ledger = CommissionLedger::where('order_line_id', $line->id)->first();
    expect($ledger)->not->toBeNull('CommissionLedger should be created');

    // Apply payment if requested
    $ledger->update([
        'payable_at'      => $deliveredAt,
        'paid_amount_usd' => $paidAmount ?? $ledger->amount_usd,
        'paid_at'         => $paidAmount > 0 ? $paidAt : null,
        'status'          => $paidAmount >= $ledger->amount_usd
            ? CommissionLedgerStatusEnum::PAID
            : CommissionLedgerStatusEnum::PARTIAL_PAID,
    ]);

    return $line->refresh();
}

// ———————————————————————————————————————
// TESTS
// ———————————————————————————————————————

it('returns correct global summary with total, pending, and avg commission', function () {
    // GIVEN: Seller and buyer
    [$sellerAccount, $seller, $sellerUser] = createAccount();
    populateProducts($seller);
    createMultiStocks($seller->id);

    $buyer = Account::factory()->create()->branches->first();

    // Create order with existing helper (has line items)
    $order1 = createOrder($buyer);
    $order2 = createOrder($buyer);

    // Update existing lines to delivered + paid
    updateLineToDelivered($order1, 10.00);  // $10 paid
    updateLineToDelivered($order2, 5.00);   // $5 paid

    // WHEN: Get global summary
    $summary = $this->service->getGlobalSummary();

    // THEN: Correct aggregation
    $totalCommission = $order1->lines->sum('commission_amount_usd') +
        $order2->lines->sum('commission_amount_usd');

    $totalPaid = 10.00 + 5.00;
    expect($summary['total_commission_usd'])->toBe((float) round($totalCommission, 2))
        ->and($summary['pending_commission_usd'])->toBe((float) round($totalCommission - $totalPaid, 2))
        ->and($summary['total_orders'])->toBe(2)
        ->and($summary['avg_commission_per_order'])->toBe((float) round($totalCommission / 2, 2));
});
it('returns zero values when no delivered lines exist', function () {
    $summary = $this->service->getGlobalSummary();

    expect($summary)->toEqual([
        'total_commission_usd'     => 0.00,
        'pending_commission_usd'   => 0.00,
        'total_orders'             => 0,
        'avg_commission_per_order' => 0,
    ]);
});

it('calculates avg commission per order correctly', function () {
    // GIVEN: Seller setup
    [$sellerAccount, $seller, $sellerUser] = createAccount();
    populateProducts();
    createMultiStocks($seller->id);

    // AND: Buyer
    $buyer = Account::factory()->create()->branches->first();

    // WHEN: Create orders with your helper (must pass seller!)
    $order1 = createOrder($buyer); // now has correct line items
    $order2 = createOrder($buyer);

    // Update existing line items to delivered + ensure ledger
    updateLineToDelivered($order1, 10.00); // $10 paid
    updateLineToDelivered($order2, 5.00);  // $5 paid

    // THEN: Get summary
    $summary = $this->service->getGlobalSummary();

    // Calculate expected values from real data
    $totalCommission = $order1->lines->sum('commission_amount_usd') +
        $order2->lines->sum('commission_amount_usd');

    $totalPaid = 10.00 + 5.00;

    expect($summary['total_commission_usd'])->toBe((float) round($totalCommission, 2))
        ->and($summary['total_orders'])->toBe(2)
        ->and($summary['avg_commission_per_order'])->toBe((float) round($totalCommission / 2, 2));
});

// ———————————————————————————————————————
// TOP ACCOUNTS
// ———————————————————————————————————————

it('returns top accounts by paid commission in last 12 months', function () {
    // ———————————————————————
    // Account 1: High Earner
    // ———————————————————————
    [$acc1, $seller1, $user1] = createAccount();

    populateProducts();
    $stocks1 = createMultiStocks($seller1->id)->pluck('id')->toArray();

    $buyer1 = Account::factory()->create()->branches->first();
    $order1 = createOrder($buyer1, $stocks1);

    // Update existing line to delivered + paid
    updateLineToDelivered($order1, 100.00); // $100 paid

    // ———————————————————————
    // Account 2: Low Earner
    // ———————————————————————
    [$acc2, $seller2, $user2] = createAccount();
    populateProducts();
    $stocks2 = createMultiStocks($seller2->id)->pluck('id')->toArray();

    $buyer2 = Account::factory()->create()->branches->first();
    $order2 = createOrder($buyer2, $stocks2);

    updateLineToDelivered($order2, 10.00); // $10 paid

    // ———————————————————————
    // WHEN: Get top accounts
    // ———————————————————————
    $this->assertNotEquals($seller1->id, $seller2->id, 'Accounts should be different');

    $top = $this->service->getTopAccounts(5);

    // ———————————————————————
    // THEN:
    // ———————————————————————

    expect($top)
        ->toHaveCount(2)
        ->and($top->first()->id)->toBe($acc1->id)
        ->and($top->first()->name)->toBe($acc1->name)
        ->and($top->first()->total_commission_usd)->toBe(100)
        ->and($top->last()->id)->toBe($acc2->id)
        ->and($top->last()->total_commission_usd)->toBe(10);
});

it('excludes accounts with no recent payments', function () {
    [$acc1, $seller1] = createAccount();
    populateProducts();
    createMultiStocks($seller1->id);
    $buyer = Account::factory()->create()->branches->first();
    $order = createOrder($buyer);

    // Paid 13 months ago → should be excluded
    $oldDelivered = now()->subMonths(13);
    updateLineToDelivered($order, 100.00, $oldDelivered, $oldDelivered);

    $top = $this->service->getTopAccounts(5);

    expect($top)->toHaveCount(0);
});

it('includes only recently paid commissions (last 12 months)', function () {
    [$acc1, $seller1] = createAccount();
    populateProducts();
    createMultiStocks($seller1->id);
    $buyer = Account::factory()->create()->branches->first();
    $order = createOrder($buyer);

    // Paid 6 months ago → included
    $delivered = now()->subMonths(6);
    updateLineToDelivered($order, 10.00, $delivered);

    $top = $this->service->getTopAccounts(5);

    expect($top)->toHaveCount(1)
        ->and($top->first()->total_commission_usd)->toBe(10);
});

// ———————————————————————————————————————
// COMMISSION TREND
// ———————————————————————————————————————

it('returns monthly commission trend correctly formatted', function () {
    [$acc, $seller] = createAccount();
    populateProducts();
    $stock1 = createMultiStocks($seller->id)->pluck('id')->toArray();
    $buyer  = Account::factory()->create()->branches->first();

    // Jan
    $order1 = createOrder($buyer);
    updateLineToDelivered($order1, 100.00, null, now()->setMonth(1)->startOfDay());

    // Feb
    $order2 = createOrder($buyer);
    updateLineToDelivered($order2, 200.00, null, now()->setMonth(2)->startOfDay());

    $from = now()->startOfYear();
    $to   = now()->endOfYear();

    $trend = $this->service->getCommissionTrend('month', $from, $to);

    expect($trend['dates'])->toEqual(['01', '02']) // DATE_FORMAT(delivered_at, "%Y-%m") → "2025-01"
        ->and($trend['labels'])->toEqual(['Jan', 'Feb'])
        ->and($trend['data'])->toEqual(['10.00', '20.00']);
})->skip();

it('returns weekly trend with YEARWEEK format', function () {
    [$acc, $seller] = createAccount();
    populateProducts();
    $buyer = Account::factory()->create()->branches->first();
    $order = createOrder($buyer);

    $thisWeek = now()->startOfWeek();
    createPaidLineWithCommission($order, 100.00, null, $thisWeek);

    $lastWeek = now()->subWeek()->startOfWeek();
    createPaidLineWithCommission($order, 50.00, null, $lastWeek);

    $trend = $this->service->getCommissionTrend('week', now()->subWeeks(4), now());

    // YEARWEEK: 202501, 202502
    $currentYearWeek = $thisWeek->format('Y').sprintf('%02d', (int) $thisWeek->format('W'));
    $lastYearWeek    = $lastWeek->format('Y').sprintf('%02d', (int) $lastWeek->format('W'));

    expect($trend['dates'])->toContain($currentYearWeek, $lastYearWeek)
        ->and($trend['labels'])->toContain('W'.substr($currentYearWeek, 4))
        ->and($trend['data'])->toHaveCount(2);
})->skip();

it('returns daily trend with formatted labels', function () {
    [$acc, $seller] = createAccount();
    populateProducts();
    $buyer = Account::factory()->create()->branches->first();
    $order = createOrder($buyer);

    $yesterday = now()->subDay()->startOfDay();
    $today     = now()->startOfDay();

    createPaidLineWithCommission($order, 100.00, null, $yesterday);
    createPaidLineWithCommission($order, 200.00, null, $today);

    $trend = $this->service->getCommissionTrend('day', now()->subDays(7), now());

    expect($trend['dates'])->toContain($yesterday->toDateString(), $today->toDateString())
        ->and($trend['labels'])->toContain($yesterday->format('d M'), $today->format('d M'))
        ->and($trend['data'])->toEqual(['10.00', '20.00']);
})->skip();

it('returns empty trend when no data in range', function () {
    $trend = $this->service->getCommissionTrend('month', now()->subYear(), now());

    expect($trend['dates'])->toBeEmpty()
        ->and($trend['labels'])->toBeEmpty()
        ->and($trend['data'])->toBeEmpty();

})->skip();
