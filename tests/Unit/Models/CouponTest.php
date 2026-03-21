<?php

/**
 * Unit Tests for Coupon Model
 *
 * This file contains unit tests for the Coupon model methods:
 * - isValid()
 * - calculateDiscount()
 * - getEligibleStockIds()
 * - hasProductRestrictions()
 */

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Variant;

describe('Coupon isValid method', function () {

    it('returns valid for active coupon without restrictions', function () {
        $coupon = Coupon::factory()->create([
            'active'           => true,
            'starting_time'    => null,
            'ending_time'      => null,
            'quantity'         => null,
            'min_order_amount' => 0,
        ]);

        $result = $coupon->isValid();

        expect($result['valid'])->toBeTrue();
    });

    it('returns invalid for inactive coupon', function () {
        $coupon = Coupon::factory()->inactive()->create();

        $result = $coupon->isValid();

        expect($result['valid'])->toBeFalse();
    });

    it('returns invalid for expired coupon', function () {
        $coupon = Coupon::factory()->expired()->create();

        $result = $coupon->isValid();

        expect($result['valid'])->toBeFalse();
        expect($result['message'])->toContain(__('This coupon has expired.'));
    });

    it('returns invalid for future coupon', function () {
        $coupon = Coupon::factory()->future()->create();

        $result = $coupon->isValid();

        expect($result['valid'])->toBeFalse();
    });

    it('returns valid for coupon within time range', function () {
        $coupon = Coupon::factory()->validTimeRange()->create([
            'min_order_amount' => 0,
        ]);

        $result = $coupon->isValid();

        expect($result['valid'])->toBeTrue();
    });

    it('returns invalid when total quantity exhausted', function () {
        $coupon = Coupon::factory()->withQuantityLimit(2)->create();

        $order1 = Order::factory()->create();
        $order2 = Order::factory()->create();

        CouponUsage::create(['coupon_id' => $coupon->id, 'order_id' => $order1->id]);
        CouponUsage::create(['coupon_id' => $coupon->id, 'order_id' => $order2->id]);

        $result = $coupon->isValid();

        expect($result['valid'])->toBeFalse();
        expect($result['message'])->toContain(__('This coupon has already been used.'));
    });

    it('returns valid when total quantity not exhausted', function () {
        $coupon = Coupon::factory()->withQuantityLimit(5)->create([
            'min_order_amount' => 0,
        ]);

        $order = Order::factory()->create();
        CouponUsage::create(['coupon_id' => $coupon->id, 'order_id' => $order->id]);

        $result = $coupon->isValid();

        expect($result['valid'])->toBeTrue();
    });

    it('returns invalid when order amount below minimum', function () {
        $coupon = Coupon::factory()->withMinOrderAmount(100)->create();

        $result = $coupon->isValid(null, 50); // Order amount is 50, minimum is 100

        expect($result['valid'])->toBeFalse();
    });

    it('returns valid when order amount meets minimum', function () {
        $coupon = Coupon::factory()->withMinOrderAmount(100)->create();

        $result = $coupon->isValid(null, 150); // Order amount is 150, minimum is 100

        expect($result['valid'])->toBeTrue();
    });

    it('returns invalid when per-customer limit reached', function () {
        $coupon   = Coupon::factory()->withPerCustomerLimit(1)->create();
        $customer = Branch::factory()->create();
        $order    = Order::factory()->create();

        CouponUsage::create([
            'coupon_id'   => $coupon->id,
            'order_id'    => $order->id,
            'customer_id' => $customer->id,
        ]);

        $result = $coupon->isValid($customer);

        expect($result['valid'])->toBeFalse();
    });

    it('returns valid when per-customer limit not reached', function () {
        $coupon   = Coupon::factory()->withPerCustomerLimit(3)->create(['min_order_amount' => 0]);
        $customer = Branch::factory()->create();
        $order    = Order::factory()->create();

        CouponUsage::create([
            'coupon_id'   => $coupon->id,
            'order_id'    => $order->id,
            'customer_id' => $customer->id,
        ]);

        $result = $coupon->isValid($customer);

        expect($result['valid'])->toBeTrue();
    });
});

describe('Coupon calculateDiscount method', function () {

    it('calculates fixed amount discount correctly', function () {
        $coupon = Coupon::factory()->amount()->create([
            'value' => 15,
        ]);

        $discount = $coupon->calculateDiscount(100);

        expect($discount)->toBe(15.0);
    });

    it('calculates percentage discount correctly', function () {
        $coupon = Coupon::factory()->percent()->create([
            'value' => 20,
        ]);

        $discount = $coupon->calculateDiscount(100);

        expect($discount)->toBe(20.0);
    });

    it('fixed amount discount does not exceed total', function () {
        $coupon = Coupon::factory()->amount()->create([
            'value' => 100,
        ]);

        $discount = $coupon->calculateDiscount(50);

        expect($discount)->toBe(50.0);
    });

    it('percentage discount is calculated correctly for different amounts', function () {
        $coupon = Coupon::factory()->percent()->create([
            'value' => 10,
        ]);

        expect($coupon->calculateDiscount(200))->toBe(20.0);
        expect($coupon->calculateDiscount(50))->toBe(5.0);
        expect($coupon->calculateDiscount(33))->toBe(3.3);
    });
});

describe('Coupon hasProductRestrictions method', function () {

    it('returns false when no restrictions exist', function () {
        $coupon = Coupon::factory()->create();

        expect($coupon->hasProductRestrictions())->toBeFalse();
    });

    it('returns true when product restrictions exist', function () {
        $coupon = Coupon::factory()->create();
        $stock  = Stock::factory()->create();

        $coupon->products()->attach($stock->id);

        expect($coupon->hasProductRestrictions())->toBeTrue();
    });

    it('returns true when brand restrictions exist', function () {
        $coupon = Coupon::factory()->create();
        $brand  = Brand::factory()->create();

        $coupon->brands()->attach($brand->id);

        expect($coupon->hasProductRestrictions())->toBeTrue();
    });

    it('returns true when category restrictions exist', function () {
        $coupon   = Coupon::factory()->create();
        $category = Category::factory()->create();

        $coupon->categories()->attach($category->id);

        expect($coupon->hasProductRestrictions())->toBeTrue();
    });
});

describe('Coupon getEligibleStockIds method', function () {

    it('returns null when no product restrictions', function () {
        $coupon = Coupon::factory()->create();

        $result = $coupon->getEligibleStockIds();

        expect($result)->toBeNull();
    });

    it('returns correct stock ids when product restrictions exist', function () {
        $branch = Branch::factory()->create();
        $coupon = Coupon::factory()->create(['branch_id' => $branch->id]);

        $stock1 = Stock::factory()->create(['branch_id' => $branch->id]);
        $stock2 = Stock::factory()->create(['branch_id' => $branch->id]);
        $stock3 = Stock::factory()->create(['branch_id' => $branch->id]);

        $coupon->products()->attach([$stock1->id, $stock2->id]);

        $result = $coupon->getEligibleStockIds();

        expect($result)->toContain($stock1->id);
        expect($result)->toContain($stock2->id);
        expect($result)->not->toContain($stock3->id);
    });

    it('returns stocks filtered by brand when brand restrictions exist', function () {
        $branch   = Branch::factory()->create();
        $brand    = Brand::factory()->create();
        $category = Category::factory()->create();

        $product1 = Product::factory()->create(['brand_id' => $brand->id, 'category_id' => $category->id]);
        $product2 = Product::factory()->create(['brand_id' => Brand::factory()->create()->id, 'category_id' => $category->id]);

        $variant1 = Variant::factory()->create(['product_id' => $product1->id]);
        $variant2 = Variant::factory()->create(['product_id' => $product2->id]);

        $stock1 = Stock::factory()->create(['branch_id' => $branch->id, 'product_id' => $product1->id, 'variant_id' => $variant1->id]);
        $stock2 = Stock::factory()->create(['branch_id' => $branch->id, 'product_id' => $product2->id, 'variant_id' => $variant2->id]);

        $coupon = Coupon::factory()->create(['branch_id' => $branch->id]);
        $coupon->brands()->attach($brand->id);

        $result = $coupon->getEligibleStockIds();

        expect($result)->toContain($stock1->id);
        expect($result)->not->toContain($stock2->id);
    });

    it('returns stocks filtered by category when category restrictions exist', function () {
        $branch   = Branch::factory()->create();
        $brand    = Brand::factory()->create();
        $category = Category::factory()->create();

        $product1 = Product::factory()->create(['brand_id' => $brand->id, 'category_id' => $category->id]);
        $product2 = Product::factory()->create(['brand_id' => $brand->id, 'category_id' => Category::factory()->create()->id]);

        $variant1 = Variant::factory()->create(['product_id' => $product1->id]);
        $variant2 = Variant::factory()->create(['product_id' => $product2->id]);

        $stock1 = Stock::factory()->create(['branch_id' => $branch->id, 'product_id' => $product1->id, 'variant_id' => $variant1->id]);
        $stock2 = Stock::factory()->create(['branch_id' => $branch->id, 'product_id' => $product2->id, 'variant_id' => $variant2->id]);

        $coupon = Coupon::factory()->create(['branch_id' => $branch->id]);
        $coupon->categories()->attach($category->id);

        $result = $coupon->getEligibleStockIds();

        expect($result)->toContain($stock1->id);
        expect($result)->not->toContain($stock2->id);
    });
});

describe('Coupon relationships', function () {

    it('belongs to branch', function () {
        $branch = Branch::factory()->create();
        $coupon = Coupon::factory()->create(['branch_id' => $branch->id]);

        expect($coupon->branch->id)->toBe($branch->id);
    });

    it('has many usages', function () {
        $coupon = Coupon::factory()->create();
        $order1 = Order::factory()->create();
        $order2 = Order::factory()->create();

        CouponUsage::create(['coupon_id' => $coupon->id, 'order_id' => $order1->id]);
        CouponUsage::create(['coupon_id' => $coupon->id, 'order_id' => $order2->id]);

        expect($coupon->usages()->count())->toBe(2);
    });

    it('morphs to many products (stocks)', function () {
        $coupon = Coupon::factory()->create();
        $stock1 = Stock::factory()->create();
        $stock2 = Stock::factory()->create();

        $coupon->products()->attach([$stock1->id, $stock2->id]);

        expect($coupon->products()->count())->toBe(2);
    });

    it('morphs to many brands', function () {
        $coupon = Coupon::factory()->create();
        $brand1 = Brand::factory()->create();
        $brand2 = Brand::factory()->create();

        $coupon->brands()->attach([$brand1->id, $brand2->id]);

        expect($coupon->brands()->count())->toBe(2);
    });

    it('morphs to many categories', function () {
        $coupon    = Coupon::factory()->create();
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        $coupon->categories()->attach([$category1->id, $category2->id]);

        expect($coupon->categories()->count())->toBe(2);
    });

    it('morphs to many customers (branches)', function () {
        $coupon    = Coupon::factory()->create();
        $customer1 = Branch::factory()->create();
        $customer2 = Branch::factory()->create();

        $coupon->customers()->attach([$customer1->id, $customer2->id]);

        expect($coupon->customers()->count())->toBe(2);
    });
});

describe('Coupon soft deletes', function () {

    it('can be soft deleted', function () {
        $coupon = Coupon::factory()->create();

        $coupon->delete();

        expect($coupon->trashed())->toBeTrue();
        expect(Coupon::withTrashed()->find($coupon->id))->not->toBeNull();
        expect(Coupon::find($coupon->id))->toBeNull();
    });

    it('can be restored', function () {
        $coupon = Coupon::factory()->create();

        $coupon->delete();
        $coupon->restore();

        expect($coupon->trashed())->toBeFalse();
        expect(Coupon::find($coupon->id))->not->toBeNull();
    });
});
