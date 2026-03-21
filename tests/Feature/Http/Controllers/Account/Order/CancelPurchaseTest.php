<?php

require_once \Pest\testDirectory().'/Feature/util.php';
require_once \Pest\testDirectory().'/Feature/Http/Controllers/Account/Stock/Helpers.php';
require_once 'Helper.php';

use function Tests\Feature\addPermissions;
use function Tests\Feature\createAccount;
use function Tests\Feature\Http\Controllers\Account\Order\createOrder;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\createMultiStocks;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\populateProducts;

describe('cancelling orders successfully', function () {
    it('has administrator job_tile', function () {
        [$account, $branch, $user] = createAccount();
        populateProducts();

        $this->actingAs($user);

        createMultiStocks(\App\Models\Account::factory()->create()->branches->first()->id);

        $order = createOrder($branch, employee: $user->userable);

        $response = $this->put(route('account.orders.cancel', ['order' => $order]));

        $response->assertStatus(302);

        $order = $order->refresh();

        expect($order->status)->toBe('cancelled');
        expect($order->lines->pluck('status')->unique()->count())->toBe(1);
        expect($order->lines->pluck('status')->first())->toBe('cancelled');
    });

    it('has `cancel purchase` permission', function () {
        [$account, $branch, $user] = createAccount('employee');
        populateProducts();
        addPermissions($user, ['cancel purchase'], 'account', 'order');

        $this->actingAs($user);

        createMultiStocks(\App\Models\Account::factory()->create()->branches->first()->id);

        $order = createOrder($branch, employee: $user->userable);

        $response = $this->put(route('account.orders.cancel', ['order' => $order]));

        $response->assertStatus(302);

        $order = $order->refresh();

        expect($order->status)->toBe('cancelled');
        expect($order->lines->pluck('status')->unique()->count())->toBe(1);
        expect($order->lines->pluck('status')->first())->toBe('cancelled');
    });
});

describe('cannot cancel Order', function () {
    it('has no `cancel purchase` permission', function () {
        [$account, $branch, $user] = createAccount('employee');
        populateProducts();

        $this->actingAs($user);

        createMultiStocks(\App\Models\Account::factory()->create()->branches->first()->id);

        $order = createOrder($branch, employee: $user->userable);

        $response = $this->put(route('account.orders.cancel', ['order' => $order]));

        $response->assertStatus(403);

        $order = $order->refresh();

        expect($order->status)->toBe('pending');
        expect($order->lines->pluck('status')->unique()->count())->toBe(1);
        expect($order->lines->pluck('status')->first())->toBe('pending');
    });

    it('not the buyer', function () {
        [$account, $branch, $user] = createAccount();
        populateProducts();

        $this->actingAs($user);

        createMultiStocks(\App\Models\Account::factory()->create()->branches->first()->id);

        $order = createOrder(\App\Models\Account::factory()->create()->branches->first(), employee: $user->userable);

        $response = $this->put(route('account.orders.cancel', ['order' => $order]));

        $response->assertStatus(403);

        $order = $order->refresh();

        expect($order->status)->toBe('pending');
        expect($order->lines->pluck('status')->unique()->count())->toBe(1);
        expect($order->lines->pluck('status')->first())->toBe('pending');
    });

    it('has line been updated by vendor', function () {
        [$account, $branch, $user] = createAccount();
        populateProducts();

        $this->actingAs($user);

        createMultiStocks(\App\Models\Account::factory()->create()->branches->first()->id);

        $order = createOrder($branch, stocks: [1, 2], employee: $user->userable);

        \App\Models\OrderLine::latest()->first()->update(['status' => 'approved']);

        $response = $this->put(route('account.orders.cancel', ['order' => $order]));

        $response->assertStatus(403);

        $order = $order->refresh();

        expect($order->status)->toBe('pending');
        expect($order->lines->pluck('status')->unique()->count())->toBe(2);
        expect($order->lines->pluck('status'))
            ->toHaveCount(2)
            ->toMatchArray(['approved', 'pending']);
    });

});
