<?php

require_once \Pest\testDirectory().'/Feature/util.php';
require_once \Pest\testDirectory().'/Feature/Http/Controllers/Account/Stock/Helpers.php';

use Illuminate\Support\Arr;

use function Tests\Feature\addAddressToBranch;
use function Tests\Feature\addPermissions;
use function Tests\Feature\createAccount;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\createMultiStocks;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\populateProducts;

function validationDataSet(): array
{
    $data = [
        'products' => [
            [
                'product_id' => 1,
                'quantity'   => 2,
                'packaging'  => 'box',
            ],
        ],
    ];

    return [
        [
            'field' => 'products.0.product_id',
            'data'  => Arr::except($data, 'products.0.product_id'),
        ],
        [
            'field' => 'products.0.quantity',
            'data'  => Arr::except($data, 'products.0.quantity'),
        ],
        [
            'field' => 'products.0.packaging',
            'data'  => Arr::except($data, 'products.0.packaging'),
        ],
        [
            'field' => 'products.0.quantity',
            'data'  => array_merge($data, ['products' => [['quantity' => -5]]]),
        ],
        [
            'field' => 'products',
            'data'  => Arr::except($data, 'products'),
        ],
    ];
}

describe('validation', function () {
    it('cannot create order with invalid data', function ($field, $data) {
        [$_, $_, $user] = createAccount();
        populateProducts();
        createMultiStocks(
            \App\Models\Account::factory()->create()->branches->first()->id,
            1,
            data: [
                'show_price' => true,
            ]
        );

        $this->actingAs($user);

        $response = $this->post(route('account.purchases.store'), $data);

        expect(\App\Models\Order::count())->toBe(0);

        $response->assertSessionHasErrors([$field]);
    })->with(validationDataSet());
});

describe('creating order successfully', function () {
    it('should create order successfully', function () {
        [$account, $branch, $user] = createAccount();
        addAddressToBranch($branch);

        populateProducts();
        createMultiStocks(
            \App\Models\Account::factory()->create()->branches->first()->id,
            data: [
                'show_price' => true,
                'price'      => 10,
            ]
        );

        $this->actingAs($user);
        $response = $this->post(route('account.purchases.store'), [
            'payment_method' => 'cash',
            'branch_id'      => $branch->id,
            'products'       => [
                [
                    'product_id' => 1,
                    'quantity'   => 2,
                    'packaging'  => \App\Models\Stock::first()->packaging,
                ],
            ],
        ]);

        $response->assertStatus(302);

        $order      = \App\Models\Order::first();
        $order_line = $order->lines()->first();

        expect(\App\Models\Order::count())->toBe(1);
        expect(\App\Models\OrderLine::count())->toBe(1);

        expect($order_line->order_id)->toBe($order->id);
        expect($order->total)->toBe(20);
        expect($order_line->total)->toBe(20);
        expect($order_line->hb_commission)->not()->toEqual(0);

        $this->assertDatabaseHas('orders', [
            'branch_id'   => $branch->id,
            'employee_id' => $user->userable_id,
            'status'      => 'pending',
        ]);
    });

    it('employee should create order with `create purchase` permission', function () {
        [$account, $branch, $user] = createAccount('employee');
        addAddressToBranch($branch);
        addPermissions($user, ['create purchase']);

        populateProducts();
        createMultiStocks(
            \App\Models\Account::factory()->create()->branches->first()->id,
            data: [
                'show_price' => true,
                'price'      => 10,
            ]
        );

        $this->actingAs($user);
        $response = $this->post(route('account.purchases.store'), [
            'payment_method' => 'cash',
            'branch_id'      => $branch->id,
            'products'       => [
                [
                    'product_id' => 1,
                    'quantity'   => 2,
                    'packaging'  => \App\Models\Stock::first()->packaging,
                ],
            ],
        ]);

        $response->assertStatus(302);

        $order      = \App\Models\Order::first();
        $order_line = $order->lines()->first();

        expect(\App\Models\Order::count())->toBe(1);
        expect(\App\Models\OrderLine::count())->toBe(1);

        expect($order_line->order_id)->toBe($order->id);
        expect($order->total)->toBe(20);
        expect($order_line->total)->toBe(20);
        expect($order_line->hb_commission)->not()->toEqual(0);
    });

    it('commission should be zero if the buyer is a customer', function () {
        [$account, $branch, $user] = createAccount();
        addAddressToBranch($branch);

        populateProducts();

        $seller_branch = \App\Models\Account::factory()->create()->branches->first();

        createMultiStocks(
            $seller_branch->id,
            data: [
                'show_price' => true,
                'price'      => 10,
            ]
        );

        $seller_branch->customers()->attach($branch);

        $this->actingAs($user);
        $response = $this->post(route('account.purchases.store'), [
            'payment_method' => 'cash',
            'branch_id'      => $branch->id,
            'products'       => [
                [
                    'product_id' => 1,
                    'quantity'   => 2,
                    'packaging'  => \App\Models\Stock::first()->packaging,
                ],
            ],
        ]);

        $response->assertStatus(302);

        $order      = \App\Models\Order::first();
        $order_line = $order->lines()->first();

        expect($order_line->order_id)->toBe($order->id);

        expect($order_line->hb_commission)->toEqual(0);
    });

});

describe('cannot create Order', function () {
    it('should not create order without `create purchase` permission', function () {
        [$account, $branch, $user] = createAccount('employee');
        addAddressToBranch($branch);

        populateProducts();
        createMultiStocks(
            \App\Models\Account::factory()->create()->branches->first()->id,
            data: [
                'show_price' => true,
            ]
        );

        $this->actingAs($user);
        $response = $this->post(route('account.purchases.store'), [
            'payment_method' => 'cash',
            'branch_id'      => $branch->id,
            'products'       => [
                [
                    'product_id' => 1,
                    'quantity'   => 2,
                    'packaging'  => \App\Models\Stock::first()->packaging,
                ],
            ],
        ]);

        $response->assertStatus(403);
        expect(\App\Models\Order::count())->toBe(0);
    });

    it('should not be able to purchase his own products', function () {
        [$account, $branch, $user] = createAccount();
        addAddressToBranch($branch);

        populateProducts();
        createMultiStocks(
            $branch->id,
            data: [
                'show_price' => true,
            ]
        );

        $this->actingAs($user);
        $response = $this->post(route('account.purchases.store'), [
            'payment_method' => 'cash',
            'branch_id'      => $branch->id,
            'products'       => [
                [
                    'product_id' => 1,
                    'quantity'   => 2,
                    'packaging'  => \App\Models\Stock::first()->packaging,
                ],
            ],
        ]);

        $response->assertStatus(403);
        expect(\App\Models\Order::count())->toBe(0);
    });

    it('should not be able to purchase if his Account is disabled', function () {
        [$account, $branch, $user] = createAccount();
        addAddressToBranch($branch);

        populateProducts();
        createMultiStocks(
            \App\Models\Account::factory()->create()->branches->first()->id,
            data: [
                'show_price' => true,
            ]
        );

        $account->update([
            'status' => 'disabled',
        ]);

        $this->actingAs($user);
        $response = $this->post(route('account.purchases.store'), [
            'payment_method' => 'cash',
            'branch_id'      => $branch->id,
            'products'       => [
                [
                    'product_id' => 1,
                    'quantity'   => 2,
                    'packaging'  => \App\Models\Stock::first()->packaging,
                ],
            ],
        ]);

        $response->assertStatus(403);
        expect(\App\Models\Order::count())->toBe(0);
    });

    it('should not be able to purchase if his Branch is disabled', function () {
        [$account, $branch, $user] = createAccount();
        addAddressToBranch($branch);

        populateProducts();
        createMultiStocks(
            \App\Models\Account::factory()->create()->branches->first()->id,
            data: [
                'show_price' => true,
            ]
        );

        $branch->update([
            'status' => 'disabled',
        ]);

        $this->actingAs($user);
        $response = $this->post(route('account.purchases.store'), [
            'payment_method' => 'cash',
            'branch_id'      => $branch->id,
            'products'       => [
                [
                    'product_id' => 1,
                    'quantity'   => 2,
                    'packaging'  => \App\Models\Stock::first()->packaging,
                ],
            ],
        ]);

        $response->assertStatus(403);
        expect(\App\Models\Order::count())->toBe(0);
    });

    it('should not be able to purchase if this branch address is not filled', function () {
        [$account, $branch, $user] = createAccount();

        populateProducts();
        createMultiStocks(
            \App\Models\Account::factory()->create()->branches->first()->id,
            data: [
                'show_price' => true,
            ]
        );

        $branch->update([
            'status' => 'disabled',
        ]);

        $this->actingAs($user);
        $response = $this->post(route('account.purchases.store'), [
            'payment_method' => 'cash',
            'branch_id'      => $branch->id,
            'products'       => [
                [
                    'product_id' => 1,
                    'quantity'   => 2,
                    'packaging'  => \App\Models\Stock::first()->packaging,
                ],
            ],
        ]);

        $response->assertStatus(403);
        expect(\App\Models\Order::count())->toBe(0);
    });

    it('should not be able to purchase disabled Stock', function () {
        [$account, $branch, $user] = createAccount();
        addAddressToBranch($branch);

        populateProducts();
        createMultiStocks(
            \App\Models\Account::factory()->create()->branches->first()->id,
            data: [
                'show_price' => true,
            ]
        );

        \App\Models\Stock::find(1)->update([
            'status' => 'disabled',
        ]);

        $this->actingAs($user);
        $response = $this->post(route('account.purchases.store'), [
            'payment_method' => 'cash',
            'branch_id'      => $branch->id,
            'products'       => [
                [
                    'product_id' => 1,
                    'quantity'   => 2,
                    'packaging'  => \App\Models\Stock::first()->packaging,
                ],
            ],
        ]);

        $response->assertStatus(403);
        expect(\App\Models\Order::count())->toBe(0);
    });

    it('should not be able to purchase quantity more than the available', function () {
        [$account, $branch, $user] = createAccount();
        addAddressToBranch($branch);

        populateProducts();
        createMultiStocks(
            \App\Models\Account::factory()->create()->branches->first()->id,
            data: [
                'show_price' => true,
            ]
        );

        \App\Models\Stock::first()->update([
            'quantity' => '1',
        ]);

        $this->actingAs($user);
        $response = $this->post(route('account.purchases.store'), [
            'payment_method' => 'cash',
            'branch_id'      => $branch->id,
            'products'       => [
                [
                    'product_id' => 1,
                    'quantity'   => 2,
                    'packaging'  => \App\Models\Stock::first()->packaging,
                ],
            ],
        ]);

        $response->assertStatus(403);
        expect(\App\Models\Order::count())->toBe(0);
    });
});
