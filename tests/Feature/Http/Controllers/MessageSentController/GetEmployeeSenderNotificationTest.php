<?php

use App\Models\Account;
use App\Models\Employee;
use App\Models\User;

beforeEach(function () {
    $this->account = Account::factory()
        ->hasBranches(4)
        ->hasEmployees()
        ->create();

    $employee = $this->account->employees()->first();

    $user = User::factory()->create([
        'userable_id'   => $employee->id,
        'userable_type' => Employee::class,
    ]);

    $this->actingAs($user);
});

describe('sender notifications', function () {

    it('should return correct branches list', function () {
        $response = $this->get('sender/notifications');

        $response->assertStatus(200)
            ->assertSee('branches');
    });

    it('should return wrong list', function () {
        $response = $this->get('sender/notifications');

        $response->assertStatus(200)
            ->assertDontSee('wrong branch');
    });

})->assignee('xmohamedamin');
