<?php

use Illuminate\Support\Arr;

require_once 'Helpers.php';
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\addPermissions;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\createAccount;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\populateProducts;

function fieldsSet()
{
    $data = [
        'product_id' => 1,
        'variant_id' => 1,
        'quantity'   => 10,
        'price'      => 150,
        'packaging'  => 'box',
    ];

    return [
        [
            'field' => 'product_id',
            'data'  => Arr::except($data, 'product_id'),
        ],
        [
            'field' => 'variant_id',
            'data'  => Arr::except($data, 'variant_id'),
        ],
        [
            'field' => 'quantity',
            'data'  => Arr::except($data, 'quantity'),
        ],
        [
            'field' => 'price',
            'data'  => Arr::except($data, 'price'),
        ],
        [
            'field' => 'packaging',
            'data'  => Arr::except($data, 'packaging'),
        ],
        [
            'field' => 'quantity',
            'data'  => array_merge($data, ['quantity' => -5]),
        ],
        [
            'field' => 'price',
            'data'  => array_merge($data, ['price' => -5]),
        ],
    ];
}

describe('Successfully adding stock', function () {
    it('can add stock', function () {
        [$account, $branch, $user] = createAccount();
        populateProducts();

        $this->actingAs($user);

        $response = $this->post(route('account.stocks.store'), [
            'product_id' => \App\Models\Product::first()->id,
            'variant_id' => \App\Models\Variant::first()->id,
            'quantity'   => 10,
            'price'      => 150,
            'packaging'  => 'box',
        ]);

        $stock = \App\Models\Stock::first();

        expect($stock->branch_id)->toBe($branch->id);
        expect(\App\Models\Stock::count())->toBe(1);
        expect($stock->quantity)->toBe(10);
        expect($stock->price)->toBe(150);
        expect($stock->packaging)->toBe('box');
        expect($stock->branch_id)->toBe($branch->id);

        $response->assertStatus(302);
    });

    it('can create stock if have create stock permission', function () {
        [$account, $branch, $user] = createAccount('employee');
        populateProducts();
        addPermissions($user, ['create stock'], 'account', 'stock');

        $this->actingAs($user);

        $response = $this->post(route('account.stocks.store'), [
            'product_id' => \App\Models\Product::first()->id,
            'variant_id' => \App\Models\Variant::first()->id,
            'quantity'   => 10,
            'price'      => 150,
            'packaging'  => 'box',
        ]);

        $stock = \App\Models\Stock::first();

        expect(\App\Models\Stock::count())->toBe(1);
        expect($stock->quantity)->toBe(10);
        expect($stock->price)->toBe(150);
        expect($stock->packaging)->toBe('box');
        expect($stock->branch_id)->toBe($branch->id);

        $response->assertStatus(302);
    });
});

describe('Rules validation', function () {
    it('cannot add stock with invalid data', function ($field, $data) {
        [$account, $branch, $user] = createAccount();
        populateProducts();

        $this->actingAs($user);

        $response = $this->post(route('account.stocks.store'), $data);

        expect(\App\Models\Stock::count())->toBe(0);

        $response->assertSessionHasErrors([$field]);
    })->with(fieldsSet());
});

describe('Failed adding stock due to lake of permission', function () {
    it('cannot add stock without permission', function () {
        [$account, $branch, $user] = createAccount('employee');
        populateProducts();

        $this->actingAs($user);

        $response = $this->post(route('account.stocks.store'), [
            'product_id' => \App\Models\Product::first()->id,
            'variant_id' => \App\Models\Variant::first()->id,
            'quantity'   => 10,
            'price'      => 150,
            'packaging'  => 'box',
        ]);

        expect(\App\Models\Stock::count())->toBe(0);

        $response->assertStatus(403);
    });
});
