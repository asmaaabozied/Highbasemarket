<?php

require_once 'Helpers.php';

use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\addPermissions;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\createAccount;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\populateProducts;

function createStock($branch_id, $variant_id = 1)
{
    return \App\Models\Stock::factory()->create([
        'branch_id'  => $branch_id,
        'variant_id' => $variant_id,
        'quantity'   => 10,
        'price'      => 150,
        'packaging'  => 'box',
    ]);
}

describe('administrator successfully deleting stock', function () {
    it('should delete stock successfully', function () {
        [$account, $branch, $user] = createAccount();

        populateProducts();

        $stock = createStock($branch->id);

        $response = $this->actingAs($user)
            ->delete(route('account.stocks.destroy', ['stock' => $stock->id]));

        $response->assertStatus(302);

        $this->assertSoftDeleted('stocks', [
            'id' => $stock->id,
        ]);
    });

    it('should delete stock successfully if has `delete stock permission`', function () {
        [$account, $branch, $user] = createAccount('employee');

        populateProducts();
        addPermissions($user, ['delete stock']);

        $stock = createStock($branch->id);

        $response = $this->actingAs($user)
            ->delete(route('account.stocks.destroy', ['stock' => $stock->id]));

        $response->assertStatus(302);

        $this->assertSoftDeleted('stocks', [
            'id' => $stock->id,
        ]);
    });
});

describe('Should not Delete a stock', function () {
    it('should not delete stock successfully if dose not have `delete stock permission`', function () {
        [$account, $branch, $user] = createAccount('employee');

        populateProducts();

        $stock = createStock($branch->id);

        $response = $this->actingAs($user)
            ->delete(route('account.stocks.destroy', ['stock' => $stock->id]));

        $response->assertStatus(403);

        $this->assertDatabaseHas('stocks', [
            'id' => $stock->id,
        ]);
    });

    it('cannot delete other account stock', function () {
        [$account, $branch, $user] = createAccount();

        populateProducts();

        $stock = createStock(\App\Models\Account::factory()->create()->branches->first()->id);

        $response = $this->actingAs($user)
            ->delete(route('account.stocks.destroy', ['stock' => $stock->id]));

        $response->assertStatus(403);

        expect(\App\Models\Stock::find($stock->id))->not()->toBeNull();
    });
});
