<?php

// tests/Feature/AccountReportServiceTest.php

use App\Enum\CommissionLedgerStatusEnum;
use App\Events\OrderLineDelivered;
use App\Http\Filters\AccountReportFilters;
use App\Models\Account;
use App\Models\CommissionLedger;
use App\Models\CommissionOverride;
use App\Services\Commission\AccountReportService;
use App\Services\Commission\CommissionReportService;

use function Tests\Feature\createAccount;
use function Tests\Feature\Http\Controllers\Account\Order\createOrder;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\createMultiStocks;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\populateProducts;

beforeEach(function () {
    $this->service = new AccountReportService;
    $this->filters = new AccountReportFilters;
});

// ———————————————————————————————————————
// TESTS
// ———————————————————————————————————————

it('returns correct commission summary for account with delivered and paid lines', function () {
    // GIVEN: Seller account and branch
    [$account, $seller, $user] = createAccount();
    populateProducts();
    createMultiStocks($seller->id);

    // AND: Buyer
    $buyer = Account::factory()->create()->branches->first();

    // WHEN: Create order using your helper
    $order = createOrder($buyer); // uses your real logic

    // THEN: Deliver a line item → should trigger OrderLineDelivered
    $lineItem = $order->lines->first();

    $lineItem->update([
        'status'       => 'delivered',
        'delivered_at' => now(),
    ]);

    event(new OrderLineDelivered($lineItem));

    $ledger = CommissionLedger::where('order_line_id', $lineItem->id)->first();

    // WHEN: Get report
    $result = $this->service->getAccountSummary($this->filters, 10);
    $data   = $result->getCollection();

    // Find this account in results
    $summary = $data->firstWhere('id', $account->id);
    expect($summary)->not->toBeNull()
        ->and($summary['total_commission_usd'])->toBe((float) round($ledger->amount_usd, 2))
        ->and($summary['paid_commission_usd'])->toBe((float) round($ledger->paid_amount_usd, 2))
        ->and($summary['pending_commission_usd'])->toBe((float) round($ledger->amount_usd - $ledger->paid_amount_usd,
            2))
        ->and($summary['total_orders'])->toBe(1)
        ->and($summary['status'])->toBe(CommissionLedgerStatusEnum::UNPAID);
});

it('returns NA status when no delivered lines exist', function () {
    [$account, $seller, $user] = createAccount();
    populateProducts();
    createMultiStocks($seller->id);

    $buyer = Account::factory()->create()->branches->first();
    $order = createOrder($buyer);

    $result  = $this->service->getAccountSummary($this->filters, 10);
    $summary = $result->getCollection()->firstWhere('id', $account->id);

    expect($summary)->not->toBeNull()
        ->and($summary['total_commission_usd'])->toBe(0.00)
        ->and($summary['total_orders'])->toBe(0)
        ->and($summary['last_payment_at'])->toBeNull()
        ->and($summary['status'])->toBe(CommissionLedgerStatusEnum::NA);
});

it('returns UNPAID status when lines are delivered but no payments made', function () {
    [$account, $seller, $user] = createAccount();
    populateProducts();
    createMultiStocks($seller->id);

    $buyer = Account::factory()->create()->branches->first();
    $order = createOrder($buyer);

    $lineItem = $order->lines->first();
    $lineItem->update(['status' => 'delivered', 'delivered_at' => now()]);

    event(new OrderLineDelivered($lineItem));

    $ledger = CommissionLedger::where('order_line_id', $lineItem->id)->first();
    $ledger->update(['payable_at' => now()->subDay()]); // but paid_amount = 0

    $result  = $this->service->getAccountSummary($this->filters, 10);
    $summary = $result->getCollection()->firstWhere('id', $account->id);

    expect($summary['status'])->toBe(CommissionLedgerStatusEnum::UNPAID)
        ->and($summary['paid_commission_usd'])->toBe(0.00);
});

it('handles multiple orders and partial payments correctly', function () {
    [$account, $seller, $user] = createAccount();
    populateProducts();
    createMultiStocks($seller->id);

    $buyer = Account::factory()->create()->branches->first();

    // Order 1
    $order1 = createOrder($buyer);
    $line1  = $order1->lines->first();
    $line1->update(['status' => 'delivered', 'delivered_at' => now()]);

    // Order 2
    $order2 = createOrder($buyer);
    $line2  = $order2->lines->first();
    $line2->update(['status' => 'delivered', 'delivered_at' => now()]);

    // Fire events
    event(new OrderLineDelivered($line1));
    event(new OrderLineDelivered($line2));

    // Set ledgers
    $ledger1 = CommissionLedger::where('order_line_id', $line1->id)->first();
    $ledger2 = CommissionLedger::where('order_line_id', $line2->id)->first();

    $ledger1->update(['payable_at' => now()->subDay(), 'paid_amount_usd' => $ledger1->amount_usd]);
    $ledger2->update(['payable_at' => now()->subDay(), 'paid_amount_usd' => 0.00]);

    $result  = $this->service->getAccountSummary($this->filters, 10);
    $summary = $result->getCollection()->firstWhere('id', $account->id);

    $total = $ledger1->amount_usd + $ledger2->amount_usd;
    $paid  = $ledger1->amount_usd;

    expect($summary['total_commission_usd'])->toBe((float) round($total, 2))
        ->and($summary['paid_commission_usd'])->toBe((float) round($paid, 2))
        ->and($summary['total_orders'])->toBe(2)
        ->and($summary['status'])->toBe(CommissionLedgerStatusEnum::PARTIAL_PAID);
});

it('applies commission override correctly', function () {
    // GIVEN: Seller & order
    [$account, $seller, $user] = createAccount();
    populateProducts();
    $stocks = createMultiStocks($seller->id)->pluck('id')->toArray();
    $buyer  = Account::factory()->create()->branches->first();
    $order  = createOrder($buyer, $stocks);

    // Deliver one line → ledger created
    $line = $order->lines->first();
    $line->update(['status' => 'delivered', 'delivered_at' => now()]);
    event(new OrderLineDelivered($line));

    $ledger = CommissionLedger::where('order_line_id', $line->id)->first();

    $originalUsd   = $ledger->amount_usd;
    $originalLocal = $line->commission_amount_local_currency;

    // WHEN: Admin overrides commission
    $admin  = \App\Models\User::factory()->create(); // your admin factory
    $newUsd = 50.00;
    $reason = 'Manual adjustment by finance';

    $service = new CommissionReportService;
    $service->overrideCommission($order->id, $newUsd, $reason, $admin->id);

    // THEN: Ledger & line should have new amounts
    $ledger->refresh();
    $line->refresh();

    $expectedLocal = round($newUsd * $line->exchange_rate_to_usd, 2);

    expect((int) $ledger->amount_usd)->toBe((int) $newUsd)
        ->and((int) $line->commission_amount_usd)->toBe((int) $newUsd);

    // AND: Override record exists with correct before/after values
    $override = CommissionOverride::where('order_line_id', $line->id)->first();

    expect($override)->not->toBeNull()
        ->and((int) $override->commission_usd_before)->toBe((int) $originalUsd)
        ->and((int) $override->commission_usd_after)->toBe((int) $newUsd)
        ->and((int) $override->commission_local_before)->toBe((int) $originalLocal)
        ->and((int) $override->commission_local_after)->toBe((int) $expectedLocal)
        ->and($override->reason)->toBe($reason)
        ->and($override->override_by_id)->toBe($admin->id);
});
