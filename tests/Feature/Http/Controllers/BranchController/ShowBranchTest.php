<?php

use App\Models\Account;

it('can show a branch profile', function () {
    $account = Account::factory()
        ->hasBranches(2)
        ->create();

    $this->assertCount(3, $account->branches);

    $response = $this->get('/storefront/profile/'.$account->branches()->first()->slug);
    $response->assertSee($account->branches()->first()->name);

    $response->assertStatus(200);

})->assignee('xmohamedamin');
