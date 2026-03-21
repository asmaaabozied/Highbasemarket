<?php

use App\Models\Account;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Employee;
use App\Models\User;

describe('accessing dashboard as account user', function () {
    it('should return the account dashboard', function () {
        $account = Account::factory()->create();

        $branch = Branch::factory()->create(['account_id' => $account->id]);

        Brand::factory()->create()->setRelation('branch', $branch);

        $employee = Employee::factory()->create(['account_id' => $account->id]);

        $user = User::factory()->create(['userable_id' => $employee->id, 'userable_type' => Employee::class]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard/Dashboard')
            ->has('brands')
            ->has('account')
            ->has('branch')
            ->has('files')
        );
    });
});
