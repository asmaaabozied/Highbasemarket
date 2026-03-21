<?php

namespace Tests\Feature\Http\Controllers\Admin\PlanController\Helpers;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Arr;

function prapperData(): array
{
    test()->withoutMiddleware(ValidateCsrfToken::class);
    //    test()->withoutExceptionHandling();

    $admin = Admin::factory()->create([
        'position' => 'administrator',
        'status'   => 'active',
    ]);

    $user = User::factory()->create([
        'userable_id'   => $admin->id,
        'userable_type' => Admin::class,
    ]);

    $countries = collect(json_decode(file_get_contents(app_path('Extends/countries.json'))))->take(4)->pluck('id');

    $account = Account::factory()
        ->hasBranches()
        ->create();

    $branch = $account->branches()->first();

    return [$user, $countries, $branch];

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

function populateFields($field, $type = 'create'): array
{

    $request = [
        'title'             => 'Plan-Global',
        'description'       => 2300000,
        'amount'            => -50,
        'attributes'        => '--List--',
        'status'            => 'active',
        'duration'          => 45,
        'plan_type'         => 'globalMarket',
        'plan_mode'         => 'paid',
        'is_auto_renewable' => true,
    ];

    $fields = [$field, 'description', 'amount'];

    $data = Arr::except($request, $field);

    return [
        $data,
        $fields,
    ];
}

function populateUser(): array
{

    test()->withoutMiddleware(ValidateCsrfToken::class);
    test()->withoutExceptionHandling();

    $account = Account::factory()
        ->hasBranches()
        ->has(
            Employee::factory()->count(1)->state([
                'job_title' => 'employee',
            ])
        )
        ->create();

    $branch = $account->branches()->first();

    $employee = $account->employees()->first();

    $user = User::factory()->create([
        'userable_id'   => $employee->id,
        'userable_type' => Employee::class,
    ]);

    return [$branch, $user];
}

function populateUpdateData($title)
{
    $data = [
        [
            'title'       => 'Plan-Service',
            'description' => 'lorem ipsum',
            'amount'      => 20,
            'attributes'  => [
                'name'      => 'Customers',
                'type'      => 'services',
                'attribute' => [
                    ['name' => 'allow', 'type' => 'select', 'value' => false, 'options' => [
                        ['option' => true],
                        ['option' => false],
                    ]],
                ],
                'status' => 1,
            ],
            'status'            => 'active',
            'duration'          => 4,
            'plan_type'         => 'globalMarket',
            'plan_mode'         => 'paid',
            'is_auto_renewable' => true,
        ],
        [
            'title'       => 'Plan-local',
            'description' => 'lorem ipsum',
            'amount'      => 10,
            'attributes'  => [
                'name'      => 'Order',
                'type'      => 'localMarket',
                'attribute' => [
                    [
                        'name'  => 'commission_amount',
                        'type'  => 'text',
                        'value' => 50,
                    ],
                    [
                        'name'  => 'is_percentage',
                        'type'  => 'checkbox',
                        'value' => false,
                    ],
                ],
                'status' => 1,
            ],
            'status'            => 'active',
            'duration'          => 7,
            'plan_type'         => 'globalMarket',
            'plan_mode'         => 'paid',
            'is_auto_renewable' => true,
        ],

        [
            'title'       => 'Plan-Global',
            'description' => 'lorem ipsum',
            'amount'      => 220,
            'attributes'  => [
                'name'      => 'Add Customer',
                'type'      => 'globalMarket',
                'attribute' => [
                    [
                        'name'  => 'numberOfRequests',
                        'type'  => 'text',
                        'value' => 21,
                    ],
                    [
                        'name'  => 'amountPerRequest',
                        'type'  => 'text',
                        'value' => 11,
                    ],
                    [
                        'name'  => 'is_percentage',
                        'type'  => 'checkbox',
                        'value' => true,
                    ],
                ],
            ],
            'status'            => 'active',
            'duration'          => 5,
            'plan_type'         => 'globalMarket',
            'plan_mode'         => 'paid',
            'is_auto_renewable' => true,
        ],
    ];

    return collect($data)->firstWhere('title', $title);
}
