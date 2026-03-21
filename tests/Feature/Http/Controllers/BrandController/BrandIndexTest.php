<?php

require_once 'Helpers.php';

use function Tests\Feature\Http\Controllers\BrandController\Helpers\createPermissions;
use function Tests\Feature\Http\Controllers\BrandController\Helpers\prapperData;

it('can show a Brand index', function () {
    [$account , $branch , $user] = prapperData();

    $this->actingAs($user);

    createPermissions($user, ['view all brands']);

    $brand = \App\Models\Brand::factory()->create();

    $response = $this->get(route('brands.index'));
    //    $response->assertSee($brand->name);

    $response->assertStatus(200);

})->assignee('xmohamedamin');
