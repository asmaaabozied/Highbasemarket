<?php

namespace Tests\Feature\Http\Controllers\Account\Coupon\Helpers;

use App\Models\Account;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use App\Models\Variant;

function createAccount($job_title = 'administrator'): array
{
    $account = Account::factory()->create();
    $branch  = $account->branches()->first();

    $employee = Employee::factory()->create([
        'account_id' => $account->id,
        'job_title'  => $job_title,
    ]);

    $user = User::factory()->create([
        'userable_id'   => $employee->id,
        'userable_type' => Employee::class,
    ]);

    return [$account, $branch, $user];
}

function addCouponPermissions(User $user, array $permissions = []): void
{
    $role = $user->getAccount()->roles()->create([
        'name' => fake()->name,
        'type' => 'account',
    ]);

    Permission::insert(
        collect($permissions)->map(function ($permission) {
            return [
                'name'   => $permission,
                'module' => 'coupons',
                'for'    => ' ',
            ];
        })->toArray()
    );

    $role->permissions()->sync(Permission::all());

    $user->roles()->attach($role->id);
}

function createVendorBranch(): array
{
    $account = Account::factory()->create();
    $branch  = $account->branches()->first();

    return [$account, $branch];
}

function populateProducts(): void
{
    Brand::factory(5)->create();
    Category::factory(5)->create();

    Product::factory(10)->create([
        'brand_id'    => Brand::query()->inRandomOrder()->first()->id,
        'category_id' => Category::query()->inRandomOrder()->first()->id,
    ]);

    Variant::factory(10)->create([
        'product_id' => Product::query()->inRandomOrder()->first()->id,
    ]);
}

function createStocksForBranch(int $branchId, int $count = 5, array $data = [])
{
    return Stock::factory($count)->create(
        array_merge([
            'branch_id'  => $branchId,
            'variant_id' => fake()->numberBetween(1, 10),
            'quantity'   => 100,
            'price'      => 10.000,
            'packaging'  => 'box',
            'status'     => 'active',
        ], $data)
    );
}

function createCoupon(int $branchId, array $data = []): Coupon
{
    return Coupon::factory()->create(array_merge([
        'branch_id' => $branchId,
    ], $data));
}

function createValidCouponData(array $overrides = []): array
{
    return array_merge([
        'name'                  => ['ar' => 'كوبون تجريبي', 'en' => 'Test Coupon'],
        'code'                  => 'TEST'.uniqid(),
        'description'           => ['ar' => 'وصف الكوبون', 'en' => 'Coupon description'],
        'value'                 => 10,
        'min_order_amount'      => 0,
        'type'                  => 'amount',
        'quantity'              => null,
        'quantity_per_customer' => null,
        'starting_time'         => null,
        'ending_time'           => null,
        'active'                => true,
        'product_ids'           => [],
        'brand_ids'             => [],
        'category_ids'          => [],
        'customer_ids'          => [],
    ], $overrides);
}

function recordCouponUsage(Coupon $coupon, Order $order, ?int $customerId = null): CouponUsage
{
    return CouponUsage::create([
        'coupon_id'   => $coupon->id,
        'order_id'    => $order->id,
        'customer_id' => $customerId,
        'user_id'     => auth()->id(),
    ]);
}
