<?php

use App\Models\Branch;
use App\Models\Brand;

require_once 'Helpers.php';

use function Tests\Feature\Http\Controllers\BrandController\Helpers\createPermissions;
use function Tests\Feature\Http\Controllers\BrandController\Helpers\prapperData;

describe('create brand', function () {

    it('can delete a brand', function () {

        [$account , $employee , $user] = prapperData();

        createPermissions($user, ['delete brand']);

        $this->actingAs($user);

        $brand = Brand::factory()->create([
            'owner_type' => Branch::class,
            'owner_id'   => $account->branches()->first()->id,
        ]);

        $response = $this->delete('brands/'.$brand->id);

        $response->assertSessionHasNoErrors()
            ->assertStatus(302);

        expect(Brand::count())->toBe(0);

    });

    it('can validate a permission', function () {

        [$account , $employee , $user] = prapperData();

        $this->actingAs($user);

        $brand = Brand::factory()->create([
            'owner_type' => Branch::class,
            'owner_id'   => $account->branches()->first()->id,
        ]);

        $response = $this->delete('brands/'.$brand->id);

        $response->assertSessionHasNoErrors()
            ->assertStatus(403);

        expect(Brand::count())->toBe(1);

    });
})->assignee('xmohamedamin');
