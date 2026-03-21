<?php

use App\Models\Account;

beforeEach(function () {
    $this->account = Account::factory()
        ->hasBranches(1)
        ->hasEmployees(1)
        ->create();
});

describe('create accout', function () {

    it('can create a account', function () {
        $this->assertNotNull($this->account->name);
    });
})->assignee('xmohamedamin');
