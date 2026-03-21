<?php

namespace Tests\Feature\Http\Controllers\NewsController\Helpers;

use App\Models\Account;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

function prapperData($branches = 1, $employees = 1)
{
    test()->withoutMiddleware(ValidateCsrfToken::class);
    //    test()->withoutExceptionHandling();

    $account = Account::factory()
        ->hasBranches($branches)
        ->has(
            Employee::factory()->count(2)->state([
                'job_title' => 'employee',
            ])
        )
        ->create();

    $employee = $account->employees()->first();

    $user = User::factory()->create([
        'userable_id'   => $employee->id,
        'userable_type' => Employee::class,
    ]);

    return $user;
}

function createPermissions(User $user, $permissions = [], $type = 'account', $module = ''): void
{
    $role = $user->getAccount()->roles()->create([
        'name' => fake()->name,
        'type' => $type,
    ]);

    Permission::insert(
        collect($permissions)->map(function ($permission) use ($module) {
            return [
                'name'   => $permission,
                'module' => $module,
                'for'    => ' ',
            ];
        })->toArray()
    );

    $role->permissions()->sync(Permission::all());

    $user->roles()->attach($role->id);
}
