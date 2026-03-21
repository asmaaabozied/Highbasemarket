<?php

require_once 'Helpers.php';

use function Tests\Feature\Http\Controllers\BranchController\Helpers\createPermissions;
use function Tests\Feature\Http\Controllers\BranchController\Helpers\prapperData;

it('can show a Branch index', function () {
    [$account , $branch , $user] = prapperData();

    $this->actingAs($user);

    createPermissions($user, ['view all branches']);

    $response = $this->get(route('account.branches.index'));
    $response->assertSee($branch->name);

    $response->assertStatus(200);

})->assignee('xmohamedamin');
