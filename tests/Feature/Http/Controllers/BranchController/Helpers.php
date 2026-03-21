<?php

namespace Tests\Feature\Http\Controllers\BranchController\Helpers;

use App\Models\Account;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

function prapperData($branches = 1, $employees = 2): array
{
    test()->withoutMiddleware(ValidateCsrfToken::class);
    test()->withoutExceptionHandling();

    $account = Account::factory()
        ->hasBranches($branches)
        ->has(
            Employee::factory()->count($employees)->state([
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

    $request = [
        'name'        => 'Create Name',
        'description' => 'update Name',
        'cr'          => 'update Name',
        'tax_number'  => '12344',
        'phone'       => ['code' => '232', 'number' => '2322222'],
        'address'     => [
            'street' => 1,
        ],
        'config' => [
            'working_days'        => 1,
            'number_of_shifts'    => '-2',
            'shift_working_hours' => [
                [
                    'start' => '08:00 UTC',
                    'end'   => '17:00 UTC',
                ],
            ],
            'enable_global_profile' => 'true',
            'enable_local_profile'  => 1234,
        ],
        'addresses' => 'address',
    ];

    $fields[] = $field;

    foreach ($request['address'] as $key => $address) {
        $fields[] = "address.$key";
    }

    foreach ($request['config'] as $config_key => $config) {

        if ($config_key === 'shift_working_hours') {

            foreach ($config as $w_key => $shift_working_hours) {
                foreach ($shift_working_hours as $shift_key => $shift) {
                    $fields[] = "config.shift_working_hours.$w_key.$shift_key";
                }

            }
            continue;
        }

        if ($config_key === 'working_days') {
            continue;
        }

        $fields[] = "config.$config_key";
    }

    $fields[] = 'config.working_days';
    $fields[] = 'addresses';

    $data = Arr::except($request, $field);

    return [
        $data,
        $fields,
    ];
}

function populateFiles()
{

    $file = UploadedFile::fake()->image('created-file.png', 640, 480);

    $response = test()->post(route('chunk-upload'), [
        'attach' => $file,
    ]);

    return $response->getContent();
}

function fake_file_envirenment()
{
    Storage::fake('local');
}
