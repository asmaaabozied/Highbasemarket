<?php

require_once \Pest\testDirectory().'/Feature/util.php';
require_once \Pest\testDirectory().'/Feature/Http/Controllers/Account/Stock/Helpers.php';
require_once 'Helper.php';

use function Tests\Feature\addPermissions;
use function Tests\Feature\createAccount;
use function Tests\Feature\Http\Controllers\Account\Order\createOrder;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\createMultiStocks;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\populateProducts;

describe('can approve Order', function () {
    it('has administrator job_tile', function () {
        [$account, $branch, $user] = createAccount();
        populateProducts();

        createMultiStocks($branch->id);

        $buyer = \App\Models\Account::factory()->create()->branches->first();

        $order = createOrder($buyer);

        $this->actingAs($user);
        $response = $this->put(route('account.orders.approve', ['order' => $order]),
            [
                'lines' => $order->lines->pluck('id')->toArray(),
            ]
        );

        $response->assertStatus(302);

        $order = $order->refresh();

        expect($order->lines->pluck('status')->unique()->count())->toBe(1);
        expect($order->lines->pluck('status')->first())->toBe('approved');
    });

    it('has `approve order` permission', function () {
        [$account, $branch, $user] = createAccount('employee');
        populateProducts();
        addPermissions($user, ['approve order'], 'account', 'order');

        createMultiStocks($branch->id);

        $buyer = \App\Models\Account::factory()->create()->branches->first();

        $order = createOrder($buyer);

        $this->actingAs($user);
        $response = $this->put(route('account.orders.approve', ['order' => $order]),
            [
                'lines' => $order->lines->pluck('id')->toArray(),
            ]
        );

        $response->assertStatus(302);

        $order = $order->refresh();

        expect($order->lines->pluck('status')->unique()->count())->toBe(1);
        expect($order->lines->pluck('status')->first())->toBe('approved');
    });
});

describe('cannot approve Order', function () {
    it('has no association with the products of the order', function () {
        [$account, $branch, $user] = createAccount();
        populateProducts();

        createMultiStocks(\App\Models\Account::factory()->create()->branches->first()->id);

        $buyer = \App\Models\Account::factory()->create()->branches->first();

        $order = createOrder($buyer);

        $this->actingAs($user);
        $response = $this->put(route('account.orders.approve', ['order' => $order]),
            [
                'lines' => $order->lines->pluck('id')->toArray(),
            ]
        );

        $response->assertStatus(403);

        $order = $order->refresh();

        expect($order->lines->pluck('status')->unique()->count())->toBe(1);
        expect($order->lines->pluck('status')->first())->toBe('pending');
    });

    it('dose not have `approve order` permission', function () {
        [$account, $branch, $user] = createAccount('employee');
        populateProducts();

        createMultiStocks($branch->id);

        $buyer = \App\Models\Account::factory()->create()->branches->first();

        $order = createOrder($buyer);

        $this->actingAs($user);
        $response = $this->put(route('account.orders.approve', ['order' => $order]),
            [
                'lines' => $order->lines->pluck('id')->toArray(),
            ]
        );

        $response->assertStatus(403);

        $order = $order->refresh();

        expect($order->lines->pluck('status')->unique()->count())->toBe(1);
        expect($order->lines->pluck('status')->first())->toBe('pending');
    });

    it('has orderLine with non pending status', function ($status) {
        [$account, $branch, $user] = createAccount();
        populateProducts();

        createMultiStocks($branch->id);

        $buyer = \App\Models\Account::factory()->create()->branches->first();

        $order = createOrder($buyer);

        $order->lines->first()->update([
            'status' => $status,
        ]);

        $this->actingAs($user);
        $response = $this->put(route('account.orders.approve', ['order' => $order]),
            [
                'lines' => $order->lines->pluck('id')->toArray(),
            ]
        );

        $response->assertStatus(403);

        $order = $order->refresh();

        expect($order->lines->pluck('status')->unique()->count())->toBe(1);
        expect($order->lines->pluck('status')->first())->toBe($status);
    })->with([
        [
            'status' => 'cancelled',
        ],
        [
            'status' => 'rejected',
        ],
        [
            'status' => 'shipped',
        ],
        [
            'status' => 'delivered',
        ],
    ]);
});
