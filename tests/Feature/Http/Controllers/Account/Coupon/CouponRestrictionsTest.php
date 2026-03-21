<?php

/**
 * Feature Tests for Coupon Restrictions Management
 *
 * This file contains tests for managing coupon restrictions:
 * - Product restrictions (specific stocks)
 * - Brand restrictions
 * - Category restrictions
 * - Customer restrictions
 */

require_once \Pest\testDirectory().'/Feature/Http/Controllers/Account/Coupon/Helpers.php';

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;

use function Tests\Feature\Http\Controllers\Account\Coupon\Helpers\createAccount;
use function Tests\Feature\Http\Controllers\Account\Coupon\Helpers\createCoupon;
use function Tests\Feature\Http\Controllers\Account\Coupon\Helpers\createStocksForBranch;
use function Tests\Feature\Http\Controllers\Account\Coupon\Helpers\createValidCouponData;
use function Tests\Feature\Http\Controllers\Account\Coupon\Helpers\createVendorBranch;
use function Tests\Feature\Http\Controllers\Account\Coupon\Helpers\populateProducts;

beforeEach(function () {
    populateProducts();
});

describe('Coupon with Product Restrictions', function () {

    it('can create coupon with product restrictions', function () {
        [$account, $branch, $user] = createAccount();
        $stocks                    = createStocksForBranch($branch->id, 3);

        $this->actingAs($user);

        $couponData = createValidCouponData([
            'product_ids' => [$stocks[0]->id, $stocks[1]->id],
        ]);

        $response = $this->post(route('account.coupons.store'), $couponData);

        $response->assertRedirect(route('account.coupons.index'));

        $coupon = Coupon::where('branch_id', $branch->id)->first();

        expect($coupon->products()->count())->toBe(2);
        expect($coupon->products()->pluck('stocks.id')->toArray())
            ->toContain($stocks[0]->id)
            ->toContain($stocks[1]->id);
    });

    it('can update coupon product restrictions', function () {
        [$account, $branch, $user] = createAccount();
        $stocks                    = createStocksForBranch($branch->id, 3);

        $coupon = createCoupon($branch->id);
        $coupon->products()->attach([$stocks[0]->id, $stocks[1]->id]);

        $this->actingAs($user);

        // Update to only have stocks[2]
        $updateData = createValidCouponData([
            'product_ids' => [$stocks[2]->id],
        ]);

        $response = $this->put(route('account.coupons.update', $coupon), $updateData);

        $response->assertRedirect(route('account.coupons.index'));

        $coupon->refresh();

        expect($coupon->products()->count())->toBe(1);
        expect($coupon->products()->first()->id)->toBe($stocks[2]->id);
    });

    it('can remove all product restrictions', function () {
        [$account, $branch, $user] = createAccount();
        $stocks                    = createStocksForBranch($branch->id, 2);

        $coupon = createCoupon($branch->id);
        $coupon->products()->attach([$stocks[0]->id, $stocks[1]->id]);

        $this->actingAs($user);

        // Update with empty product_ids
        $updateData = createValidCouponData([
            'product_ids' => [],
        ]);

        $response = $this->put(route('account.coupons.update', $coupon), $updateData);

        $response->assertRedirect(route('account.coupons.index'));

        $coupon->refresh();

        expect($coupon->products()->count())->toBe(0);
    });

    it('shows product restrictions in edit page', function () {
        [$account, $branch, $user] = createAccount();
        $stocks                    = createStocksForBranch($branch->id, 2);

        $coupon = createCoupon($branch->id);
        $coupon->products()->attach([$stocks[0]->id, $stocks[1]->id]);

        $this->actingAs($user);

        $response = $this->get(route('account.coupons.edit', $coupon));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('Accounts/Coupons/Edit')
                ->has('selectedProducts', 2)
        );
    });
});

describe('Coupon with Brand Restrictions', function () {

    it('can create coupon with brand restrictions', function () {
        [$account, $branch, $user] = createAccount();
        $brands                    = Brand::query()->take(2)->get();

        $this->actingAs($user);

        $couponData = createValidCouponData([
            'brand_ids' => $brands->pluck('id')->toArray(),
        ]);

        $response = $this->post(route('account.coupons.store'), $couponData);

        $response->assertRedirect(route('account.coupons.index'));

        $coupon = Coupon::where('branch_id', $branch->id)->first();

        expect($coupon->brands()->count())->toBe(2);
    });

    it('can update coupon brand restrictions', function () {
        [$account, $branch, $user] = createAccount();
        $brands                    = Brand::query()->take(3)->get();

        $coupon = createCoupon($branch->id);
        $coupon->brands()->attach([$brands[0]->id, $brands[1]->id]);

        $this->actingAs($user);

        // Update to only have brands[2]
        $updateData = createValidCouponData([
            'brand_ids' => [$brands[2]->id],
        ]);

        $response = $this->put(route('account.coupons.update', $coupon), $updateData);

        $response->assertRedirect(route('account.coupons.index'));

        $coupon->refresh();

        expect($coupon->brands()->count())->toBe(1);
        expect($coupon->brands()->first()->id)->toBe($brands[2]->id);
    });

    it('shows brand restrictions in edit page', function () {
        [$account, $branch, $user] = createAccount();
        $brands                    = Brand::query()->take(2)->get();

        $coupon = createCoupon($branch->id);
        $coupon->brands()->attach($brands->pluck('id')->toArray());

        $this->actingAs($user);

        $response = $this->get(route('account.coupons.edit', $coupon));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('Accounts/Coupons/Edit')
                ->has('selectedBrands', 2)
        );
    });
});

describe('Coupon with Category Restrictions', function () {

    it('can create coupon with category restrictions', function () {
        [$account, $branch, $user] = createAccount();
        $categories                = Category::query()->take(2)->get();

        $this->actingAs($user);

        $couponData = createValidCouponData([
            'category_ids' => $categories->pluck('id')->toArray(),
        ]);

        $response = $this->post(route('account.coupons.store'), $couponData);

        $response->assertRedirect(route('account.coupons.index'));

        $coupon = Coupon::where('branch_id', $branch->id)->first();

        expect($coupon->categories()->count())->toBe(2);
    });

    it('can update coupon category restrictions', function () {
        [$account, $branch, $user] = createAccount();
        $categories                = Category::query()->take(3)->get();

        $coupon = createCoupon($branch->id);
        $coupon->categories()->attach([$categories[0]->id, $categories[1]->id]);

        $this->actingAs($user);

        // Update to only have categories[2]
        $updateData = createValidCouponData([
            'category_ids' => [$categories[2]->id],
        ]);

        $response = $this->put(route('account.coupons.update', $coupon), $updateData);

        $response->assertRedirect(route('account.coupons.index'));

        $coupon->refresh();

        expect($coupon->categories()->count())->toBe(1);
        expect($coupon->categories()->first()->id)->toBe($categories[2]->id);
    });

    it('shows category restrictions in edit page', function () {
        [$account, $branch, $user] = createAccount();
        $categories                = Category::query()->take(2)->get();

        $coupon = createCoupon($branch->id);
        $coupon->categories()->attach($categories->pluck('id')->toArray());

        $this->actingAs($user);

        $response = $this->get(route('account.coupons.edit', $coupon));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('Accounts/Coupons/Edit')
                ->has('selectedCategories', 2)
        );
    });
});

describe('Coupon with Customer Restrictions', function () {

    it('can create coupon with customer restrictions', function () {
        [$account, $branch, $user]         = createAccount();
        [$otherAccount1, $customerBranch1] = createVendorBranch();
        [$otherAccount2, $customerBranch2] = createVendorBranch();

        $this->actingAs($user);

        $couponData = createValidCouponData([
            'customer_ids' => [$customerBranch1->id, $customerBranch2->id],
        ]);

        $response = $this->post(route('account.coupons.store'), $couponData);

        $response->assertRedirect(route('account.coupons.index'));

        $coupon = Coupon::where('branch_id', $branch->id)->first();

        expect($coupon->customers()->count())->toBe(2);
    });

    it('can update coupon customer restrictions', function () {
        [$account, $branch, $user]         = createAccount();
        [$otherAccount1, $customerBranch1] = createVendorBranch();
        [$otherAccount2, $customerBranch2] = createVendorBranch();
        [$otherAccount3, $customerBranch3] = createVendorBranch();

        $coupon = createCoupon($branch->id);
        $coupon->customers()->attach([$customerBranch1->id, $customerBranch2->id]);

        $this->actingAs($user);

        // Update to only have customerBranch3
        $updateData = createValidCouponData([
            'customer_ids' => [$customerBranch3->id],
        ]);

        $response = $this->put(route('account.coupons.update', $coupon), $updateData);

        $response->assertRedirect(route('account.coupons.index'));

        $coupon->refresh();

        expect($coupon->customers()->count())->toBe(1);
        expect($coupon->customers()->first()->id)->toBe($customerBranch3->id);
    });

    it('shows customer restrictions in edit page', function () {
        [$account, $branch, $user]         = createAccount();
        [$otherAccount1, $customerBranch1] = createVendorBranch();
        [$otherAccount2, $customerBranch2] = createVendorBranch();

        $coupon = createCoupon($branch->id);
        $coupon->customers()->attach([$customerBranch1->id, $customerBranch2->id]);

        $this->actingAs($user);

        $response = $this->get(route('account.coupons.edit', $coupon));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('Accounts/Coupons/Edit')
                ->has('selectedCustomers', 2)
        );
    });
});

describe('Coupon with Multiple Restrictions', function () {

    it('can create coupon with multiple restriction types', function () {
        [$account, $branch, $user] = createAccount();
        $stocks                    = createStocksForBranch($branch->id, 2);
        $brands                    = Brand::query()->take(2)->get();
        $categories                = Category::query()->take(2)->get();

        [$otherAccount, $customerBranch] = createVendorBranch();

        $this->actingAs($user);

        $couponData = createValidCouponData([
            'product_ids'  => [$stocks[0]->id],
            'brand_ids'    => [$brands[0]->id],
            'category_ids' => [$categories[0]->id],
            'customer_ids' => [$customerBranch->id],
        ]);

        $response = $this->post(route('account.coupons.store'), $couponData);

        $response->assertRedirect(route('account.coupons.index'));

        $coupon = Coupon::where('branch_id', $branch->id)->first();

        expect($coupon->products()->count())->toBe(1);
        expect($coupon->brands()->count())->toBe(1);
        expect($coupon->categories()->count())->toBe(1);
        expect($coupon->customers()->count())->toBe(1);
    });

    it('hasProductRestrictions returns true when any product restriction exists', function () {
        [$account, $branch, $user] = createAccount();
        $stocks                    = createStocksForBranch($branch->id, 1);

        $coupon = createCoupon($branch->id);

        expect($coupon->hasProductRestrictions())->toBeFalse();

        $coupon->products()->attach($stocks[0]->id);

        expect($coupon->hasProductRestrictions())->toBeTrue();
    });

    it('hasProductRestrictions returns true when brand restriction exists', function () {
        [$account, $branch, $user] = createAccount();
        $brands                    = Brand::query()->take(1)->get();

        $coupon = createCoupon($branch->id);

        $coupon->brands()->attach($brands[0]->id);

        expect($coupon->hasProductRestrictions())->toBeTrue();
    });

    it('hasProductRestrictions returns true when category restriction exists', function () {
        [$account, $branch, $user] = createAccount();
        $categories                = Category::query()->take(1)->get();

        $coupon = createCoupon($branch->id);

        $coupon->categories()->attach($categories[0]->id);

        expect($coupon->hasProductRestrictions())->toBeTrue();
    });
});

describe('Show Page Displays Restrictions', function () {

    it('shows all restriction types in show page', function () {
        [$account, $branch, $user] = createAccount();
        $stocks                    = createStocksForBranch($branch->id, 2);
        $brands                    = Brand::query()->take(2)->get();
        $categories                = Category::query()->take(2)->get();

        [$otherAccount, $customerBranch] = createVendorBranch();

        $coupon = createCoupon($branch->id);
        $coupon->products()->attach($stocks->pluck('id')->toArray());
        $coupon->brands()->attach($brands->pluck('id')->toArray());
        $coupon->categories()->attach($categories->pluck('id')->toArray());
        $coupon->customers()->attach($customerBranch->id);

        $this->actingAs($user);

        $response = $this->get(route('account.coupons.show', $coupon));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('Accounts/Coupons/Show')
                ->has('products', 2)
                ->has('brands', 2)
                ->has('categories', 2)
                ->has('customers', 1)
        );
    });
});
