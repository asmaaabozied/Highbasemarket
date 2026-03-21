<?php

require_once 'Helpers.php';

use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\addPermissions;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\createAccount;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\createStock;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\populateProducts;

describe('toggle stock status', function () {
    it('can toggle stock status', function () {
        [$account, $branch, $user] = createAccount();

        populateProducts();

        $stock1 = createStock($branch->id);

        $stock1->update([
            'status' => 'disabled',
        ]);

        $response = $this->actingAs($user)->put(route('status.toggle', [
            'model'  => 'stock',
            'status' => 'active',
            'id'     => $stock1->id,
        ]));

        $stock1 = $stock1->fresh();

        $response->assertStatus(302);
        expect($stock1->status)->toBe('active');
    });

    it('employee with `update Stock` permission can change the status', function () {
        [$account, $branch, $user] = createAccount('employee');

        addPermissions($user, ['update stock'], 'account', 'stock');
        populateProducts();

        $stock1 = createStock($branch->id);

        $stock1->update([
            'status' => 'disabled',
        ]);

        $response = $this->actingAs($user)->put(route('status.toggle', [
            'model'  => 'stock',
            'status' => 'active',
            'id'     => $stock1->id,
        ]));

        $stock1 = $stock1->fresh();

        $response->assertStatus(302);
        expect($stock1->status)->toBe('active');

        $response = $this->actingAs($user)->put(route('status.toggle', [
            'model'  => 'stock',
            'status' => 'disabled',
            'id'     => $stock1->id,
        ]));

        $stock1 = $stock1->fresh();
        expect($stock1->status)->toBe('disabled');
    });
});

describe('should not change stock status', function () {
    it('should not change stock status if user does not have `update stock` permission', function () {
        [$account, $branch, $user] = createAccount('employee');

        populateProducts();

        $stock1 = createStock($branch->id);

        $stock1->update([
            'status' => 'disabled',
        ]);

        $response = $this->actingAs($user)->put(route('status.toggle', [
            'model'  => 'stock',
            'status' => 'active',
            'id'     => $stock1->id,
        ]));

        $stock1 = $stock1->fresh();

        $response->assertStatus(403);
        expect($stock1->status)->toBe('disabled');
    });

    it('should not be able to change other account stock status', function () {
        [$account, $branch, $user] = createAccount('employee');

        populateProducts();

        $stock1 = createStock(\App\Models\Account::factory()->create()->branches->first()->id);

        $stock1->update([
            'status' => 'disabled',
        ]);

        $response = $this->actingAs($user)->put(route('status.toggle', [
            'model'  => 'stock',
            'status' => 'active',
            'id'     => $stock1->id,
        ]));

        $stock1 = $stock1->fresh();

        $response->assertStatus(403);
        expect($stock1->status)->toBe('disabled');
    });
});
