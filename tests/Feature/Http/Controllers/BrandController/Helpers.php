<?php

namespace Tests\Feature\Http\Controllers\BrandController\Helpers;

use App\Models\Account;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Arr;

function prapperData($branches = 1, $employees = 1): array
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

    $branch = $account->branches()->first();

    return [
        $account,
        $branch,
        $user,
    ];
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

    if ($type === 'create') {
        $brands = [
            ['name'                => 'Branch name',
                'description'      => 'Brand New Description',
                'logo'             => 'http://placehold.it/640x480.png/00ff00?text=Created+Brand',
                'collection_image' => 'http://placehold.it/640x480.png/00ff00?text=Created+Brand',
                'ownership_type'   => 'distributor'],
        ];

        $data   = [];
        $fields = [];

        foreach ($brands as $key => $branch) {
            if (! in_array("brands.$key.$field", $fields)) {
                $fields[] = "brands.$key.$field";
            }

            $data[] = Arr::except($branch, $field);
        }

        return [
            $data,
            $fields,
        ];
    }

    $brand = [
        'name'             => 'Branch name',
        'description'      => 'Brand New Description',
        'logo'             => 'http://placehold.it/640x480.png/00ff00?text=Created+Brand',
        'collection_image' => 'http://placehold.it/640x480.png/00ff00?text=Created+Brand',
        'ownership_type'   => 'buyer',
    ];

    $data[]   = Arr::except($brand, $field);
    $fields[] = $field;

    return [
        $data,
        $fields,
    ];
}
