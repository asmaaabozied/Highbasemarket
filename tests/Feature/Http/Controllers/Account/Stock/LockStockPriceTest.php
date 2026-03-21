<?php

require_once 'Helpers.php';

use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\addPermissions;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\createAccount;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\createMultiStocks;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\populateProducts;

describe('locking and unlocking stock prices for administrator', function () {
    it('should lock branch stock price', function () {
        [$account, $branch, $user] = createAccount();

        populateProducts();

        createMultiStocks($branch->id);
        createMultiStocks(\App\Models\Account::factory()->create()->branches->first()->id);

        $response = $this->actingAs($user)->put(route('account.stocks.price-locker', [
            'lock' => true,
        ]));

        $stocks = $branch->stocks()->select('show_price')->distinct()->get();

        expect($stocks->count())->toBe(1);
        expect($stocks->first()->show_price)->toBe(0);
    });

    it('should unlock branch stock price', function () {
        [$account, $branch, $user] = createAccount();

        populateProducts();

        createMultiStocks($branch->id);
        createMultiStocks(\App\Models\Account::factory()->create()->branches->first()->id);

        $response = $this->actingAs($user)->put(route('account.stocks.price-locker', [
            'lock' => false,
        ]));

        $stocks = $branch->stocks()->select('show_price')->distinct()->get();

        // Check if the branch stocks are unlocked
        expect($stocks->count())->toBe(1);
        expect($stocks->first()->show_price)->toBe(1);

        // check other branch stocks
        $other_stocks = \App\Models\Stock::where('branch_id', '!=', $branch->id)
            ->select('show_price')
            ->distinct()
            ->get();

        expect($other_stocks->count())->toBe(1);
        expect($other_stocks->first()->show_price)->toBe(0);
    });
});

describe('locking and unlocking prices for a non administrator', function () {
    it('should lock branch stock price if has `update stock` permission', function () {
        [$account, $branch, $user] = createAccount('employee');

        populateProducts();

        createMultiStocks($branch->id);
        createMultiStocks(\App\Models\Account::factory()->create()->branches->first()->id);

        addPermissions($user, ['update stock'], 'account', 'stock');

        $response = $this->actingAs($user)->put(route('account.stocks.price-locker', [
            'lock' => false,
        ]));

        $stocks = $branch->stocks()->select('show_price')->distinct()->get();

        expect($stocks->count())->toBe(1);
        expect($stocks->first()->show_price)->toBe(1);
    });

    it('should not be able to unlock the price if has no `update stock` permission', function () {
        [$account, $branch, $user] = createAccount('employee');

        populateProducts();

        createMultiStocks($branch->id);
        createMultiStocks(\App\Models\Account::factory()->create()->branches->first()->id);

        $response = $this->actingAs($user)->put(route('account.stocks.price-locker', [
            'lock' => false,
        ]));

        $stocks = $branch->stocks()->select('show_price')->distinct()->get();

        $response->assertStatus(403);
        expect($stocks->count())->toBe(1);
        expect($stocks->first()->show_price)->toBe(0);
    });
});
