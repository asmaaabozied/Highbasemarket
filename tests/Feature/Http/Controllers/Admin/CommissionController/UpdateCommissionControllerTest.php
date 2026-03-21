<?php

use App\Events\OrderLineDelivered;
use App\Models\Account;
use App\Models\CommissionLedger;
use App\Models\CommissionOverride;

use function Pest\Laravel\put;
use function Tests\Feature\createAccount;
use function Tests\Feature\Http\Controllers\Account\Order\createOrder;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\createMultiStocks;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\populateProducts;
use function Tests\Feature\Http\Controllers\Admin\PlanController\Helpers\prapperData;

function prepareCommissionTestContext()
{
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

    return [$order, $line, $ledger, $originalUsd, $originalLocal];
}

it('applies commission override correctly', function () {
    [$order, $line, $ledger, $originalUsd, $originalLocal] = prepareCommissionTestContext();

    // WHEN: Admin overrides commission via route
    [$admin] = prapperData();
    $newUsd  = 50.00;
    $reason  = 'Manual adjustment by finance';

    $this->actingAs($admin);

    $response = put(route('admins.commissions.update', ['order' => $order->uuid]), [
        'amount' => $newUsd,
        'reason' => $reason,
    ]);

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Commissions/Show')
            ->where('message', __('Commission override applied successfully.'))
        );

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

it('fails validation if amount or reason is missing', function () {
    [$order] = prepareCommissionTestContext();
    [$admin] = prapperData();
    $this->actingAs($admin);

    // Missing both fields
    $response = put(route('admins.commissions.update', ['order' => $order->uuid]), []);
    $response->assertSessionHasErrors(['amount', 'reason']);

    // Missing amount only
    $response = put(route('admins.commissions.update', ['order' => $order->uuid]), [
        'reason' => 'Some reason',
    ]);
    $response->assertSessionHasErrors(['amount']);

    // Missing reason only
    $response = put(route('admins.commissions.update', ['order' => $order->uuid]), [
        'amount' => 100,
    ]);
    $response->assertSessionHasErrors(['reason']);
});
