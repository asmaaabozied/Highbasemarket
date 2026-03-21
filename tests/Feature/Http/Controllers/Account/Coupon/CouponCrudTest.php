<?php

/**
 * Feature Tests for Coupon CRUD Operations
 *
 * This file contains tests for creating, reading, updating, and deleting coupons.
 */

require_once \Pest\testDirectory().'/Feature/Http/Controllers/Account/Coupon/Helpers.php';

use App\Models\Coupon;

use function Tests\Feature\Http\Controllers\Account\Coupon\Helpers\addCouponPermissions;
use function Tests\Feature\Http\Controllers\Account\Coupon\Helpers\createAccount;
use function Tests\Feature\Http\Controllers\Account\Coupon\Helpers\createCoupon;
use function Tests\Feature\Http\Controllers\Account\Coupon\Helpers\createValidCouponData;
use function Tests\Feature\Http\Controllers\Account\Coupon\Helpers\populateProducts;

beforeEach(function () {
    populateProducts();
});

describe('Coupon Listing', function () {

    it('administrator can view coupons list', function () {
        [$account, $branch, $user] = createAccount();
        createCoupon($branch->id);
        createCoupon($branch->id);

        $this->actingAs($user);

        $response = $this->get(route('account.coupons.index'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('Accounts/Coupons/Index')
                ->has('coupons.data', 2)
        );
    });

    it('employee with permission can view coupons list', function () {
        [$account, $branch, $user] = createAccount('employee');
        addCouponPermissions($user, ['view all coupons']);
        createCoupon($branch->id);

        $this->actingAs($user);

        $response = $this->get(route('account.coupons.index'));

        $response->assertStatus(200);
    });

    it('only shows coupons for current branch', function () {
        [$account, $branch, $user] = createAccount();

        // Create coupons for current branch
        createCoupon($branch->id);
        createCoupon($branch->id);

        // Create coupon for another branch
        $otherBranch = \App\Models\Account::factory()->create()->branches->first();
        createCoupon($otherBranch->id);

        $this->actingAs($user);

        $response = $this->get(route('account.coupons.index'));

        $response->assertInertia(
            fn ($page) => $page
                ->has('coupons.data', 2)
        );
    });
});

describe('Coupon Creation', function () {

    it('administrator can create coupon with valid data', function () {
        [$account, $branch, $user] = createAccount();

        $this->actingAs($user);

        $couponData = createValidCouponData([
            'code' => 'TESTCODE123',
        ]);

        $response = $this->post(route('account.coupons.store'), $couponData);

        $response->assertRedirect(route('account.coupons.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('coupons', [
            'branch_id' => $branch->id,
            'code'      => 'TESTCODE123',
            'type'      => 'amount',
        ]);
    });

    it('can create percentage coupon', function () {
        [$account, $branch, $user] = createAccount();

        $this->actingAs($user);

        $couponData = createValidCouponData([
            'type'  => 'percent',
            'value' => 15,
        ]);

        $response = $this->post(route('account.coupons.store'), $couponData);

        $response->assertRedirect(route('account.coupons.index'));

        $this->assertDatabaseHas('coupons', [
            'branch_id' => $branch->id,
            'type'      => 'percent',
            'value'     => 15,
        ]);
    });

    it('can create coupon with time restrictions', function () {
        [$account, $branch, $user] = createAccount();

        $this->actingAs($user);

        $startTime = now()->addDay()->format('Y-m-d H:i:s');
        $endTime   = now()->addMonth()->format('Y-m-d H:i:s');

        $couponData = createValidCouponData([
            'starting_time' => $startTime,
            'ending_time'   => $endTime,
        ]);

        $response = $this->post(route('account.coupons.store'), $couponData);

        $response->assertRedirect(route('account.coupons.index'));

        $coupon = Coupon::where('branch_id', $branch->id)->first();
        expect($coupon->starting_time)->not->toBeNull();
        expect($coupon->ending_time)->not->toBeNull();
    });

    it('can create coupon with quantity limits', function () {
        [$account, $branch, $user] = createAccount();

        $this->actingAs($user);

        $couponData = createValidCouponData([
            'quantity'              => 100,
            'quantity_per_customer' => 5,
        ]);

        $response = $this->post(route('account.coupons.store'), $couponData);

        $response->assertRedirect(route('account.coupons.index'));

        $this->assertDatabaseHas('coupons', [
            'branch_id'             => $branch->id,
            'quantity'              => 100,
            'quantity_per_customer' => 5,
        ]);
    });

    it('can create coupon with minimum order amount', function () {
        [$account, $branch, $user] = createAccount();

        $this->actingAs($user);

        $couponData = createValidCouponData([
            'min_order_amount' => 50,
        ]);

        $response = $this->post(route('account.coupons.store'), $couponData);

        $response->assertRedirect(route('account.coupons.index'));

        $this->assertDatabaseHas('coupons', [
            'branch_id'        => $branch->id,
            'min_order_amount' => 50,
        ]);
    });

    it('can create inactive coupon', function () {
        [$account, $branch, $user] = createAccount();

        $this->actingAs($user);

        $couponData = createValidCouponData([
            'active' => false,
        ]);

        $response = $this->post(route('account.coupons.store'), $couponData);

        $response->assertRedirect(route('account.coupons.index'));

        $this->assertDatabaseHas('coupons', [
            'branch_id' => $branch->id,
            'active'    => false,
        ]);
    });
});

describe('Coupon Creation Validation', function () {

    it('requires coupon name in arabic', function () {
        [$account, $branch, $user] = createAccount();

        $this->actingAs($user);

        $couponData = createValidCouponData();
        unset($couponData['name']['ar']);

        $response = $this->post(route('account.coupons.store'), $couponData);

        $response->assertSessionHasErrors(['name.ar']);
    });

    it('requires coupon name in english', function () {
        [$account, $branch, $user] = createAccount();

        $this->actingAs($user);

        $couponData = createValidCouponData();
        unset($couponData['name']['en']);

        $response = $this->post(route('account.coupons.store'), $couponData);

        $response->assertSessionHasErrors(['name.en']);
    });

    it('requires coupon code', function () {
        [$account, $branch, $user] = createAccount();

        $this->actingAs($user);

        $couponData = createValidCouponData();
        unset($couponData['code']);

        $response = $this->post(route('account.coupons.store'), $couponData);

        $response->assertSessionHasErrors(['code']);
    });

    it('requires unique coupon code within same branch', function () {
        [$account, $branch, $user] = createAccount();

        createCoupon($branch->id, ['code' => 'UNIQUE123']);

        $this->actingAs($user);

        $couponData = createValidCouponData([
            'code' => 'UNIQUE123',
        ]);

        $response = $this->post(route('account.coupons.store'), $couponData);

        $response->assertSessionHasErrors(['code']);
    });

    it('allows same coupon code in different branches', function () {
        [$account, $branch, $user] = createAccount();

        // Create coupon in another branch with same code
        $otherBranch = \App\Models\Account::factory()->create()->branches->first();
        createCoupon($otherBranch->id, ['code' => 'SAMECODE']);

        $this->actingAs($user);

        $couponData = createValidCouponData([
            'code' => 'SAMECODE',
        ]);

        $response = $this->post(route('account.coupons.store'), $couponData);

        $response->assertRedirect(route('account.coupons.index'));
        $response->assertSessionHas('success');
    });

    it('requires value to be greater than 0', function () {
        [$account, $branch, $user] = createAccount();

        $this->actingAs($user);

        $couponData = createValidCouponData([
            'value' => 0,
        ]);

        $response = $this->post(route('account.coupons.store'), $couponData);

        $response->assertSessionHasErrors(['value']);
    });

    it('requires valid coupon type', function () {
        [$account, $branch, $user] = createAccount();

        $this->actingAs($user);

        $couponData = createValidCouponData([
            'type' => 'invalid',
        ]);

        $response = $this->post(route('account.coupons.store'), $couponData);

        $response->assertSessionHasErrors(['type']);
    });

    it('ending time must be after starting time', function () {
        [$account, $branch, $user] = createAccount();

        $this->actingAs($user);

        $couponData = createValidCouponData([
            'starting_time' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'ending_time'   => now()->addDays(2)->format('Y-m-d H:i:s'),
        ]);

        $response = $this->post(route('account.coupons.store'), $couponData);

        $response->assertSessionHasErrors(['ending_time']);
    });

    it('name cannot exceed 50 characters', function () {
        [$account, $branch, $user] = createAccount();

        $this->actingAs($user);

        $couponData = createValidCouponData([
            'name' => [
                'ar' => str_repeat('أ', 51),
                'en' => str_repeat('a', 51),
            ],
        ]);

        $response = $this->post(route('account.coupons.store'), $couponData);

        $response->assertSessionHasErrors(['name.ar', 'name.en']);
    });
});

describe('Coupon Update', function () {

    it('administrator can update coupon', function () {
        [$account, $branch, $user] = createAccount();
        $coupon                    = createCoupon($branch->id, ['code' => 'OLDCODE']);

        $this->actingAs($user);

        $updateData = createValidCouponData([
            'code'  => 'NEWCODE',
            'value' => 25,
        ]);

        $response = $this->put(route('account.coupons.update', $coupon), $updateData);

        $response->assertRedirect(route('account.coupons.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('coupons', [
            'id'    => $coupon->id,
            'code'  => 'NEWCODE',
            'value' => 25,
        ]);
    });

    it('employee with permission can update coupon', function () {
        [$account, $branch, $user] = createAccount('employee');
        addCouponPermissions($user, ['update coupon']);
        $coupon = createCoupon($branch->id);

        $this->actingAs($user);

        $updateData = createValidCouponData([
            'value' => 30,
        ]);

        $response = $this->put(route('account.coupons.update', $coupon), $updateData);

        $response->assertRedirect(route('account.coupons.index'));
    });

    it('employee without permission cannot update coupon', function () {
        [$account, $branch, $user] = createAccount('employee');
        $coupon                    = createCoupon($branch->id);

        $this->actingAs($user);

        $updateData = createValidCouponData();

        $response = $this->put(route('account.coupons.update', $coupon), $updateData);

        $response->assertStatus(403);
    });

    it('can update coupon code to same value', function () {
        [$account, $branch, $user] = createAccount();
        $coupon                    = createCoupon($branch->id, ['code' => 'SAMECODE']);

        $this->actingAs($user);

        $updateData = createValidCouponData([
            'code' => 'SAMECODE',
        ]);

        $response = $this->put(route('account.coupons.update', $coupon), $updateData);

        $response->assertRedirect(route('account.coupons.index'));
        $response->assertSessionHas('success');
    });
});

describe('Coupon Deletion', function () {

    it('administrator can delete coupon', function () {
        [$account, $branch, $user] = createAccount();
        $coupon                    = createCoupon($branch->id);

        $this->actingAs($user);

        $response = $this->delete(route('account.coupons.destroy', $coupon));

        $response->assertRedirect(route('account.coupons.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('coupons', [
            'id' => $coupon->id,
        ]);
    });

    it('employee with permission can delete coupon', function () {
        [$account, $branch, $user] = createAccount('employee');
        addCouponPermissions($user, ['delete coupon']);
        $coupon = createCoupon($branch->id);

        $this->actingAs($user);

        $response = $this->delete(route('account.coupons.destroy', $coupon));

        $response->assertRedirect(route('account.coupons.index'));
    });

    it('employee without permission cannot delete coupon', function () {
        [$account, $branch, $user] = createAccount('employee');
        $coupon                    = createCoupon($branch->id);

        $this->actingAs($user);

        $response = $this->delete(route('account.coupons.destroy', $coupon));

        $response->assertStatus(403);

        $this->assertDatabaseHas('coupons', [
            'id'         => $coupon->id,
            'deleted_at' => null,
        ]);
    });
});

describe('Coupon Show', function () {

    it('administrator can view coupon details', function () {
        [$account, $branch, $user] = createAccount();
        $coupon                    = createCoupon($branch->id);

        $this->actingAs($user);

        $response = $this->get(route('account.coupons.show', $coupon));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('Accounts/Coupons/Show')
                ->has('coupon')
        );
    });

    it('employee with permission can view coupon details', function () {
        [$account, $branch, $user] = createAccount('employee');
        addCouponPermissions($user, ['view coupon']);
        $coupon = createCoupon($branch->id);

        $this->actingAs($user);

        $response = $this->get(route('account.coupons.show', $coupon));

        $response->assertStatus(200);
    });

    it('employee without permission cannot view coupon details', function () {
        [$account, $branch, $user] = createAccount('employee');
        $coupon                    = createCoupon($branch->id);

        $this->actingAs($user);

        $response = $this->get(route('account.coupons.show', $coupon));

        $response->assertStatus(403);
    });
});

describe('Coupon Create Page', function () {

    it('administrator can access create coupon page', function () {
        [$account, $branch, $user] = createAccount();

        $this->actingAs($user);

        $response = $this->get(route('account.coupons.create'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('Accounts/Coupons/Create')
        );
    });
});

describe('Coupon Edit Page', function () {

    it('administrator can access edit coupon page', function () {
        [$account, $branch, $user] = createAccount();
        $coupon                    = createCoupon($branch->id);

        $this->actingAs($user);

        $response = $this->get(route('account.coupons.edit', $coupon));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('Accounts/Coupons/Edit')
                ->has('coupon')
        );
    });

    it('employee with permission can access edit coupon page', function () {
        [$account, $branch, $user] = createAccount('employee');
        addCouponPermissions($user, ['update coupon']);
        $coupon = createCoupon($branch->id);

        $this->actingAs($user);

        $response = $this->get(route('account.coupons.edit', $coupon));

        $response->assertStatus(200);
    });

    it('employee without permission cannot access edit coupon page', function () {
        [$account, $branch, $user] = createAccount('employee');
        $coupon                    = createCoupon($branch->id);

        $this->actingAs($user);

        $response = $this->get(route('account.coupons.edit', $coupon));

        $response->assertStatus(403);
    });
});
