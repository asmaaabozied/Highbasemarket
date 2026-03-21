<?php

namespace Tests\Feature\Http\Controllers\Account\Stock;

use App\Models\Account;

require_once 'Helpers.php';

use Illuminate\Support\Arr;

use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\addPermissions;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\createAccount;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\createStock;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\populateProducts;

function fieldsSet()
{
    $data = [
        'variant_id' => 1,
        'quantity'   => 20,
        'price'      => 200,
        'packaging'  => 'box',
        'tiers'      => [],
        'vat'        => 6,
        'show_price' => 1,
        'config'     => [
            'display_on_highbase' => true,
        ],
        'rrp'             => 5,
        'moq'             => 5,
        'expiration_date' => '2024-01-01',
    ];

    return [
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
        [
            'field' => 'rrp',
            'data'  => array_merge($data, ['rrp' => -5]),
        ],
        [
            'field' => 'moq',
            'data'  => array_merge($data, ['moq' => -5]),
        ],
        [
            'field' => 'expiration_date',
            'data'  => array_merge($data, ['expiration_date' => -5]),
        ],
    ];

}

describe('updating stock successfully', function () {
    it('bulk update stock', function () {
        [$account, $branch, $user] = createAccount();

        populateProducts();

        $stock1 = createStock($branch->id);

        $stock2 = createStock(Account::factory()->create()->branches()->first()->id, 2);

        $response = $this->actingAs($user)->put(route('account.stocks.bulk-update'), [
            'products' => [
                [
                    'id'         => $stock1->id,
                    'variant_id' => $stock1->variant_id,
                    'quantity'   => 20,
                    'price'      => 200,
                    'packaging'  => 'box',
                    'tiers'      => [],
                    'vat'        => 0,
                    'show_price' => 1,
                ],
                [
                    'id'         => $stock2->id,
                    'variant_id' => $stock2->variant_id,
                    'quantity'   => 30,
                    'price'      => 300,
                    'packaging'  => 'box',
                    'tiers'      => [],
                    'vat'        => 0,
                    'show_price' => 1,
                ],
            ],
        ]);

        $response->assertStatus(302);

        $stock1 = $stock1->fresh();
        $stock2 = $stock2->fresh();

        expect($stock1->quantity)->toBe(20)
            ->and($stock1->price)->toBe(200)
            ->and($stock1->packaging)->toBe('box')
            ->and($stock2->quantity)->toBe(10)
            ->and($stock2->price)->toBe(150)
            ->and($stock2->packaging)->toBe('box');

        // should not update the stock in another branch
    });

    it('Update Product Stock', function () {
        [$account, $branch, $user] = createAccount();

        populateProducts();

        $stock1 = createStock($branch->id);

        $stock2 = createStock(Account::factory()->create()->branches()->first()->id, 2);

        $response = $this->actingAs($user)->put(route('account.stocks.update'), [
            'variants' => [
                [
                    'variant_id' => $stock1->variant_id,
                    'quantity'   => 20,
                    'price'      => 200,
                    'packaging'  => 'box',
                    'tiers'      => [],
                    'vat'        => 6,
                    'show_price' => 1,
                    'config'     => [
                        'display_on_highbase' => true,
                    ],
                    'rrp'             => 5,
                    'moq'             => 5,
                    'expiration_date' => '2024-01-01',
                ],
                [
                    'variant_id' => $stock2->variant_id,
                    'quantity'   => 30,
                    'price'      => 300,
                    'packaging'  => 'box',
                    'tiers'      => [],
                    'vat'        => 0,
                    'show_price' => 1,
                    'config'     => [
                        'display_on_highbase' => true,
                    ],
                    'rrp'             => 5,
                    'moq'             => 5,
                    'expiration_date' => '2024-01-01',
                ],
            ],
        ]);

        $response->assertStatus(302);

        $stock1 = $stock1->fresh();
        $stock2 = $stock2->fresh();

        expect($stock1->quantity)->toBe(20)
            ->and($stock1->price)->toBe(200)
            ->and($stock1->packaging)->toBe('box')
            ->and($stock1->vat)->toBe(6)
            ->and($stock1->rrp)->toBe(5)
            ->and($stock1->moq)->toBe(5)
            ->and($stock1->expiration_date)->toBe('2024-01-01')
            ->and($stock1->config['display_on_highbase'])->toBe(true)
            ->and($stock2->quantity)->toBe(10)
            ->and($stock2->price)->toBe(150)
            ->and($stock2->packaging)->toBe('box')
            ->and($branch->stocks()->count())->toBe(2);

        // should not update the stock in another branch

    });
});

describe('validating stock update', function () {
    it('validates stock update', function ($field, $data) {
        [$account, $branch, $user] = createAccount();

        populateProducts();

        $stock1 = createStock($branch->id);

        $response = $this->actingAs($user)->put(route('account.stocks.update'), [
            'variants' => [
                $data,
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            "variants.0.$field",
        ]);
    })->with(fieldsSet());
});

describe('Checking Permission for a any user without administrator job_title', function () {
    it('cannot update Stock without update stock permission', function () {
        [$account, $branch, $user] = createAccount('employee');

        populateProducts();

        $stock1 = createStock($branch->id);

        $response = $this->actingAs($user)->put(route('account.stocks.update'), [
            'variants' => [
                [
                    'variant_id' => $stock1->variant_id,
                    'quantity'   => 20,
                    'price'      => 200,
                    'packaging'  => 'box',
                    'tiers'      => [],
                    'vat'        => 6,
                    'show_price' => 1,
                    'config'     => [
                        'display_on_highbase' => true,
                    ],
                    'rrp'             => 5,
                    'moq'             => 5,
                    'expiration_date' => '2024-01-01',
                ],
            ],
        ]);

        $response->assertStatus(403);

        expect(\App\Models\Stock::count())->toBe(1)
            ->and($stock1->quantity)->toBe(10)
            ->and($stock1->price)->toBe(150)
            ->and($stock1->packaging)->toBe('box');

    });

    it('can update Stock if hast `update stock` permission', function () {
        [$account, $branch, $user] = createAccount('employee');

        populateProducts();
        addPermissions($user, ['update stock'], 'account', 'stock');

        $stock1 = createStock($branch->id);

        $response = $this->actingAs($user)->put(route('account.stocks.update'), [
            'variants' => [
                [
                    'variant_id' => $stock1->variant_id,
                    'quantity'   => 20,
                    'price'      => 200,
                    'packaging'  => 'box',
                    'tiers'      => [],
                    'vat'        => 6,
                    'show_price' => 1,
                    'config'     => [
                        'display_on_highbase' => true,
                    ],
                    'rrp'             => 5,
                    'moq'             => 5,
                    'expiration_date' => '2024-01-01',
                ],
            ],
        ]);

        $response->assertStatus(302);

        $stock1 = $stock1->fresh();

        expect(\App\Models\Stock::count())->toBe(1)
            ->and($stock1->quantity)->toBe(20)
            ->and($stock1->price)->toBe(200)
            ->and($stock1->packaging)->toBe('box')
            ->and($stock1->vat)->toBe(6)
            ->and($stock1->rrp)->toBe(5);
    });
});
