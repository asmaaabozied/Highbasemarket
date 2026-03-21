<?php

require_once 'Helpers.php';

use function Tests\Feature\Http\Controllers\BranchController\Helpers\createPermissions;
use function Tests\Feature\Http\Controllers\BranchController\Helpers\prapperData;

describe('delete branch', function () {
    it('it can delete a branch', function () {

        [$account , $branch , $user] = prapperData(0);

        createPermissions($user, ['delete branch']);

        $response = $this->actingAs($user)->delete('/account/branches/'.$branch->id);

        $response->assertStatus(302)
            ->assertSessionDoesntHaveErrors();

        expect($account->refresh()->branches->count())->toBe(0);
    });

    it('it can validate a permission', function () {

        [$account , $branch , $user] = prapperData(0);

        $response = $this->actingAs($user)->delete('/account/branches/'.$branch->id);

        $response->assertStatus(403)
            ->assertSessionDoesntHaveErrors();

        expect($account->refresh()->branches->count())->toBe(1);
    });
})->assignee('xmohamedamin');
