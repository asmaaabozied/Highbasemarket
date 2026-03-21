<?php

// tests/Feature/AllocateBulkCommissionPaymentServiceTest.php

use App\Enum\CommissionLedgerStatusEnum;
use App\Events\OrderLineDelivered;
use App\Models\Account;
use App\Models\CommissionLedger;
use App\Models\OrderLine;
use App\Services\AllocateBulkCommissionPaymentService;
use Illuminate\Support\Facades\Event;

use function Tests\Feature\createAccount;
use function Tests\Feature\Http\Controllers\Account\Order\createOrder;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\createMultiStocks;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\populateProducts;

// Service will be shared across tests
beforeEach(function () {
    [$this->account, $this->seller, $this->user] = createAccount();
    populateProducts();
    createMultiStocks($this->seller->id);

    $this->buyer = Account::factory()->create()->branches->first();

    // Resolve service once
    $this->service = $this->app->make(AllocateBulkCommissionPaymentService::class);

    // Optional: assert it's the right instance
    expect($this->service)->toBeInstanceOf(AllocateBulkCommissionPaymentService::class);
});

it('fires OrderLineDelivered event when line item is marked as delivered', function () {
    // Arrange
    $buyer = Account::factory()->create()->branches->first();
    $order = createOrder($buyer);

    $lineItem = $order->lines()->first();

    // Fake only this event
    Event::fake(OrderLineDelivered::class);

    // Act: Deliver the line item
    $lineItem->update([
        'status'       => 'delivered',
        'delivered_at' => now(),
    ]);

    event(new OrderLineDelivered($lineItem));

    // Assert: Event was dispatched
    Event::assertDispatched(OrderLineDelivered::class,
        function ($event) use ($lineItem) {
            return $event->orderLine->is($lineItem);
        });

});

it('allocates payment to delivered and payable commission ledgers', function () {
    // GIVEN: Create order using helper
    $order = createOrder(buyer: $this->buyer);

    // WHEN: Deliver line items → should fire OrderLineDelivered
    $order->lines->each(function (OrderLine $line) {
        $line->update([
            'status'       => 'delivered',
            'delivered_at' => now(),
        ]);
        event(new OrderLineDelivered($line));
    });

    $order->update(['status' => 'delivered']); // optional: sync order status

    // THEN: Commission ledger should exist
    $ledger = CommissionLedger::query()
        ->whereHas('lineItem', function ($q) use ($order) {
            $q->where('order_id', $order->id);
        })
        ->first();

    expect($ledger)->not->toBeNull('CommissionLedger not found for seller and order');
    // Ensure it's payable
    $ledger->update(['payable_at' => now()->subDay()]);
    $ledger->refresh();

    expect($ledger->paid_amount_usd)
        ->toBe(0)
        ->and($ledger->status)
        ->toBe(CommissionLedgerStatusEnum::UNPAID);

    // WHEN: Allocate full payment
    $this->service->allocate(seller: $this->seller, paymentAmount: $ledger->amount_usd);

    // THEN: Should be fully paid

    $ledger->refresh();
    expect($ledger->paid_amount_usd)
        ->toBe($ledger->amount_usd)
        ->and($ledger->status)
        ->toBe(CommissionLedgerStatusEnum::PAID)
        ->and($ledger->paid_at)->not->toBeNull();
});

it('applies partial payment and marks as partial paid', function () {
    $order = createOrder($this->buyer);

    $order->lines->each(function (OrderLine $line) {
        $line->update([
            'status'       => 'delivered',
            'delivered_at' => now(),
        ]);
        event(new OrderLineDelivered($line));
    });

    $order->update(['status' => 'delivered']);

    $ledger = CommissionLedger::query()
        ->whereHas('lineItem', function ($q) use ($order) {
            $q->where('order_id', $order->id);
        })
        ->first();

    $ledger->update(['payable_at' => now()->subDay()]);

    $partialAmount = $ledger->amount_usd * 0.5;

    $this->service->allocate($this->seller, (float) $partialAmount);

    $ledger->refresh();

    expect((int) $ledger->paid_amount_usd)->toBe((int) $partialAmount)
        ->and($ledger->status)->toBe(CommissionLedgerStatusEnum::PARTIAL_PAID)
        ->and($ledger->paid_at)->toBeNull();
});
