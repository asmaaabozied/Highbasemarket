<?php

namespace Tests\Feature\Http\Controllers\Account\Stock\Helpers;

use App\Models\Category;
use App\Models\Permission;
use App\Models\User;

function createAccount($job_title = 'administrator')
{
    $account = \App\Models\Account::factory()->create();
    $branch  = $account->branches()->first();

    $employee = \App\Models\Employee::factory()->create([
        'account_id' => $account->id,
        'job_title'  => $job_title,
    ]);

    $user = \App\Models\User::factory()->create([
        'userable_id'   => $employee->id,
        'userable_type' => \App\Models\Employee::class,
    ]);

    return [$account, $branch, $user];
}

function addPermissions(User $user, $permissions = [], $type = 'account', $module = ''): void
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

function populateProducts()
{
    $brands     = \App\Models\Brand::factory(10)->create();
    $categories = Category::factory(10)->create();

    $products = \App\Models\Product::factory(10)->create([
        'brand_id'    => $brands->random()->id,
        'category_id' => $categories->random()->id,
    ]);

    \App\Models\Variant::factory(10)->create([
        'product_id' => $products->random()->id,
    ]);
}

function createStock($branch_id, $variant_id = 1)
{
    return \App\Models\Stock::factory()->create([
        'branch_id'  => $branch_id,
        'variant_id' => $variant_id,
        'quantity'   => 10,
        'price'      => 150,
        'packaging'  => 'box',
    ]);
}

function createMultiStocks($branch_id, $count = 5, array $data = [])
{
    return \App\Models\Stock::factory($count)->create(
        array_merge([
            'branch_id'  => $branch_id,
            'variant_id' => fake()->numberBetween(1, 10),
            'quantity'   => 10,
            'price'      => 150,
            'packaging'  => 'box',
            'status'     => 'active',
        ], $data)
    );
}
