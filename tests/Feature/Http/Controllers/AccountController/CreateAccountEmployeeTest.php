<?php

use App\Models\Account;
use App\Models\Employee;
use App\Models\User;

beforeEach(function () {
    $this->account = Account::factory()
        ->hasBranches(1)
        ->hasEmployees(1)
        ->create();
});

describe('create account employee', function () {

    it('can reference employee to account', function () {

        $employee = $this->account->employees()->first();

        $user = User::factory()->create([
            'userable_id'   => $employee->id,
            'userable_type' => Employee::class,
        ]);

        $this->assertEquals($user->userable_id, $employee->id);
        $this->assertEquals($user->userable_type, Employee::class);
    });
})->assignee('xmohamedamin');
