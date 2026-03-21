<?php

/**
 * Feature Tests for Coupon Validation Logic
 *
 * This file contains tests for validating coupons through the storefront API.
 * Tests cover coupon validity checks, restrictions, and discount calculations.
 */

require_once \Pest\testDirectory().'/Feature/Http/Controllers/Account/Coupon/Helpers.php';

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Stock;

use function Tests\Feature\Http\Controllers\Account\Coupon\Helpers\createAccount;
use function Tests\Feature\Http\Controllers\Account\Coupon\Helpers\createCoupon;
use function Tests\Feature\Http\Controllers\Account\Coupon\Helpers\createStocksForBranch;
use function Tests\Feature\Http\Controllers\Account\Coupon\Helpers\createVendorBranch;
use function Tests\Feature\Http\Controllers\Account\Coupon\Helpers\populateProducts;

beforeEach(function () {
    populateProducts();
});

describe('Coupon Validation - Basic', function () {

    it('validates a valid active coupon successfully', function () {
        [$vendorAccount, $vendorBranch]                    = createVendorBranch();
        [$customerAccount, $customerBranch, $customerUser] = createAccount();

        $stocks = createStocksForBranch($vendorBranch->id, 2);

        $coupon = createCoupon($vendorBranch->id, [
            'type'             => 'amount',
            'value'            => 10,
            'min_order_amount' => 0,
            'active'           => true,
        ]);

        $this->actingAs($customerUser);

        $response = $this->postJson(route('storefront.validate-coupon'), [
            'code'      => $coupon->code,
            'branch_id' => $vendorBranch->id,
            'items'     => [
                ['stock_id' => $stocks[0]->id, 'total' => 50],
                ['stock_id' => $stocks[1]->id, 'total' => 30],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'  => true,
            'discount' => 10,
        ]);
    });

    it('rejects invalid coupon code', function () {
        [$vendorAccount, $vendorBranch]                    = createVendorBranch();
        [$customerAccount, $customerBranch, $customerUser] = createAccount();

        $stocks = createStocksForBranch($vendorBranch->id, 1);

        $this->actingAs($customerUser);

        $response = $this->postJson(route('storefront.validate-coupon'), [
            'code'      => 'INVALID_CODE',
            'branch_id' => $vendorBranch->id,
            'items'     => [
                ['stock_id' => $stocks[0]->id, 'total' => 50],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    });

    it('rejects inactive coupon', function () {
        [$vendorAccount, $vendorBranch]                    = createVendorBranch();
        [$customerAccount, $customerBranch, $customerUser] = createAccount();

        $stocks = createStocksForBranch($vendorBranch->id, 1);

        $coupon = createCoupon($vendorBranch->id, [
            'active' => false,
        ]);

        $this->actingAs($customerUser);

        $response = $this->postJson(route('storefront.validate-coupon'), [
            'code'      => $coupon->code,
            'branch_id' => $vendorBranch->id,
            'items'     => [
                ['stock_id' => $stocks[0]->id, 'total' => 50],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    });
});

describe('Coupon Validation - Time Restrictions', function () {

    it('rejects expired coupon', function () {
        [$vendorAccount, $vendorBranch]                    = createVendorBranch();
        [$customerAccount, $customerBranch, $customerUser] = createAccount();

        $stocks = createStocksForBranch($vendorBranch->id, 1);

        $coupon = Coupon::factory()->expired()->create([
            'branch_id' => $vendorBranch->id,
        ]);

        $this->actingAs($customerUser);

        $response = $this->postJson(route('storefront.validate-coupon'), [
            'code'      => $coupon->code,
            'branch_id' => $vendorBranch->id,
            'items'     => [
                ['stock_id' => $stocks[0]->id, 'total' => 50],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    });

    it('rejects coupon with future start date', function () {
        [$vendorAccount, $vendorBranch]                    = createVendorBranch();
        [$customerAccount, $customerBranch, $customerUser] = createAccount();

        $stocks = createStocksForBranch($vendorBranch->id, 1);

        $coupon = Coupon::factory()->future()->create([
            'branch_id' => $vendorBranch->id,
        ]);

        $this->actingAs($customerUser);

        $response = $this->postJson(route('storefront.validate-coupon'), [
            'code'      => $coupon->code,
            'branch_id' => $vendorBranch->id,
            'items'     => [
                ['stock_id' => $stocks[0]->id, 'total' => 50],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    });

    it('accepts coupon within valid time range', function () {
        [$vendorAccount, $vendorBranch]                    = createVendorBranch();
        [$customerAccount, $customerBranch, $customerUser] = createAccount();

        $stocks = createStocksForBranch($vendorBranch->id, 1);

        $coupon = Coupon::factory()->validTimeRange()->create([
            'branch_id'        => $vendorBranch->id,
            'type'             => 'amount',
            'value'            => 5,
            'min_order_amount' => 0,
        ]);

        $this->actingAs($customerUser);

        $response = $this->postJson(route('storefront.validate-coupon'), [
            'code'      => $coupon->code,
            'branch_id' => $vendorBranch->id,
            'items'     => [
                ['stock_id' => $stocks[0]->id, 'total' => 50],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    });
});

describe('Coupon Validation - Quantity Limits', function () {

    it('rejects coupon when total quantity exhausted', function () {
        [$vendorAccount, $vendorBranch]                    = createVendorBranch();
        [$customerAccount, $customerBranch, $customerUser] = createAccount();

        $stocks = createStocksForBranch($vendorBranch->id, 1);

        $coupon = Coupon::factory()->withQuantityLimit(2)->create([
            'branch_id'        => $vendorBranch->id,
            'min_order_amount' => 0,
        ]);

        // Create 2 usages (exhausting the limit)
        $order1 = Order::factory()->create(['branch_id' => $customerBranch->id]);
        $order2 = Order::factory()->create(['branch_id' => $customerBranch->id]);

        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'order_id'  => $order1->id,
        ]);
        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'order_id'  => $order2->id,
        ]);

        $this->actingAs($customerUser);

        $response = $this->postJson(route('storefront.validate-coupon'), [
            'code'      => $coupon->code,
            'branch_id' => $vendorBranch->id,
            'items'     => [
                ['stock_id' => $stocks[0]->id, 'total' => 50],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    });

    it('accepts coupon when quantity not exhausted', function () {
        [$vendorAccount, $vendorBranch]                    = createVendorBranch();
        [$customerAccount, $customerBranch, $customerUser] = createAccount();

        $stocks = createStocksForBranch($vendorBranch->id, 1);

        $coupon = Coupon::factory()->withQuantityLimit(5)->create([
            'branch_id'        => $vendorBranch->id,
            'type'             => 'amount',
            'value'            => 10,
            'min_order_amount' => 0,
        ]);

        // Create only 2 usages (still 3 remaining)
        $order1 = Order::factory()->create(['branch_id' => $customerBranch->id]);
        $order2 = Order::factory()->create(['branch_id' => $customerBranch->id]);

        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'order_id'  => $order1->id,
        ]);
        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'order_id'  => $order2->id,
        ]);

        $this->actingAs($customerUser);

        $response = $this->postJson(route('storefront.validate-coupon'), [
            'code'      => $coupon->code,
            'branch_id' => $vendorBranch->id,
            'items'     => [
                ['stock_id' => $stocks[0]->id, 'total' => 50],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    });

    it('rejects coupon when per-customer limit reached', function () {
        [$vendorAccount, $vendorBranch]                    = createVendorBranch();
        [$customerAccount, $customerBranch, $customerUser] = createAccount();

        $stocks = createStocksForBranch($vendorBranch->id, 1);

        $coupon = Coupon::factory()->withPerCustomerLimit(1)->create([
            'branch_id'        => $vendorBranch->id,
            'min_order_amount' => 0,
        ]);

        // Create 1 usage for this customer
        $order = Order::factory()->create(['branch_id' => $customerBranch->id]);

        CouponUsage::create([
            'coupon_id'   => $coupon->id,
            'order_id'    => $order->id,
            'customer_id' => $customerBranch->id,
        ]);

        $this->actingAs($customerUser);

        $response = $this->postJson(route('storefront.validate-coupon'), [
            'code'      => $coupon->code,
            'branch_id' => $vendorBranch->id,
            'items'     => [
                ['stock_id' => $stocks[0]->id, 'total' => 50],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    });

    it('accepts coupon when per-customer limit not reached', function () {
        [$vendorAccount, $vendorBranch]                    = createVendorBranch();
        [$customerAccount, $customerBranch, $customerUser] = createAccount();

        $stocks = createStocksForBranch($vendorBranch->id, 1);

        $coupon = Coupon::factory()->withPerCustomerLimit(3)->create([
            'branch_id'        => $vendorBranch->id,
            'type'             => 'amount',
            'value'            => 10,
            'min_order_amount' => 0,
        ]);

        // Create 1 usage for this customer (still 2 remaining)
        $order = Order::factory()->create(['branch_id' => $customerBranch->id]);

        CouponUsage::create([
            'coupon_id'   => $coupon->id,
            'order_id'    => $order->id,
            'customer_id' => $customerBranch->id,
        ]);

        $this->actingAs($customerUser);

        $response = $this->postJson(route('storefront.validate-coupon'), [
            'code'      => $coupon->code,
            'branch_id' => $vendorBranch->id,
            'items'     => [
                ['stock_id' => $stocks[0]->id, 'total' => 50],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    });
});

describe('Coupon Validation - Minimum Order Amount', function () {

    it('rejects coupon when order amount is below minimum', function () {
        [$vendorAccount, $vendorBranch]                    = createVendorBranch();
        [$customerAccount, $customerBranch, $customerUser] = createAccount();

        $stocks = createStocksForBranch($vendorBranch->id, 1);

        $coupon = Coupon::factory()->withMinOrderAmount(100)->create([
            'branch_id' => $vendorBranch->id,
        ]);

        $this->actingAs($customerUser);

        $response = $this->postJson(route('storefront.validate-coupon'), [
            'code'      => $coupon->code,
            'branch_id' => $vendorBranch->id,
            'items'     => [
                ['stock_id' => $stocks[0]->id, 'total' => 50], // Only 50, minimum is 100
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    });

    it('accepts coupon when order amount meets minimum', function () {
        [$vendorAccount, $vendorBranch]                    = createVendorBranch();
        [$customerAccount, $customerBranch, $customerUser] = createAccount();

        $stocks = createStocksForBranch($vendorBranch->id, 2);

        $coupon = Coupon::factory()->withMinOrderAmount(100)->create([
            'branch_id' => $vendorBranch->id,
            'type'      => 'amount',
            'value'     => 20,
        ]);

        $this->actingAs($customerUser);

        $response = $this->postJson(route('storefront.validate-coupon'), [
            'code'      => $coupon->code,
            'branch_id' => $vendorBranch->id,
            'items'     => [
                ['stock_id' => $stocks[0]->id, 'total' => 60],
                ['stock_id' => $stocks[1]->id, 'total' => 50], // Total: 110
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    });
});

describe('Coupon Discount Calculation', function () {

    it('calculates fixed amount discount correctly', function () {
        [$vendorAccount, $vendorBranch]                    = createVendorBranch();
        [$customerAccount, $customerBranch, $customerUser] = createAccount();

        $stocks = createStocksForBranch($vendorBranch->id, 1);

        $coupon = createCoupon($vendorBranch->id, [
            'type'             => 'amount',
            'value'            => 15,
            'min_order_amount' => 0,
        ]);

        $this->actingAs($customerUser);

        $response = $this->postJson(route('storefront.validate-coupon'), [
            'code'      => $coupon->code,
            'branch_id' => $vendorBranch->id,
            'items'     => [
                ['stock_id' => $stocks[0]->id, 'total' => 100],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'      => true,
            'coupon_type'  => 'amount',
            'coupon_value' => 15,
            'discount'     => 15,
            'new_total'    => 85,
        ]);
    });

    it('calculates percentage discount correctly', function () {
        [$vendorAccount, $vendorBranch]                    = createVendorBranch();
        [$customerAccount, $customerBranch, $customerUser] = createAccount();

        $stocks = createStocksForBranch($vendorBranch->id, 1);

        $coupon = createCoupon($vendorBranch->id, [
            'type'             => 'percent',
            'value'            => 20, // 20%
            'min_order_amount' => 0,
        ]);

        $this->actingAs($customerUser);

        $response = $this->postJson(route('storefront.validate-coupon'), [
            'code'      => $coupon->code,
            'branch_id' => $vendorBranch->id,
            'items'     => [
                ['stock_id' => $stocks[0]->id, 'total' => 100],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'      => true,
            'coupon_type'  => 'percent',
            'coupon_value' => 20,
            'discount'     => 20, // 20% of 100
            'new_total'    => 80,
        ]);
    });

    it('amount discount does not exceed order total', function () {
        [$vendorAccount, $vendorBranch]                    = createVendorBranch();
        [$customerAccount, $customerBranch, $customerUser] = createAccount();

        $stocks = createStocksForBranch($vendorBranch->id, 1);

        $coupon = createCoupon($vendorBranch->id, [
            'type'             => 'amount',
            'value'            => 100, // Larger than order total
            'min_order_amount' => 0,
        ]);

        $this->actingAs($customerUser);

        $response = $this->postJson(route('storefront.validate-coupon'), [
            'code'      => $coupon->code,
            'branch_id' => $vendorBranch->id,
            'items'     => [
                ['stock_id' => $stocks[0]->id, 'total' => 50], // Only 50
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'   => true,
            'discount'  => 50, // Cannot exceed 50
            'new_total' => 0,
        ]);
    });
});

describe('Coupon Product Restrictions', function () {

    it('applies discount only to eligible products', function () {
        [$vendorAccount, $vendorBranch]                    = createVendorBranch();
        [$customerAccount, $customerBranch, $customerUser] = createAccount();

        $stocks = createStocksForBranch($vendorBranch->id, 2);

        $coupon = createCoupon($vendorBranch->id, [
            'type'             => 'percent',
            'value'            => 50, // 50%
            'min_order_amount' => 0,
        ]);

        // Attach only the first stock as eligible
        $coupon->products()->attach($stocks[0]->id);

        $this->actingAs($customerUser);

        $response = $this->postJson(route('storefront.validate-coupon'), [
            'code'      => $coupon->code,
            'branch_id' => $vendorBranch->id,
            'items'     => [
                ['stock_id' => $stocks[0]->id, 'total' => 100], // Eligible
                ['stock_id' => $stocks[1]->id, 'total' => 100], // Not eligible
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'  => true,
            'discount' => 50, // 50% of 100 (only eligible product)
        ]);
    });

    it('rejects coupon when cart has no eligible products', function () {
        [$vendorAccount, $vendorBranch]                    = createVendorBranch();
        [$customerAccount, $customerBranch, $customerUser] = createAccount();

        $stocks = createStocksForBranch($vendorBranch->id, 2);

        $coupon = createCoupon($vendorBranch->id, [
            'type'             => 'percent',
            'value'            => 10,
            'min_order_amount' => 0,
        ]);

        // Attach a different stock that's not in the cart
        $otherStock = Stock::factory()->create(['branch_id' => $vendorBranch->id]);
        $coupon->products()->attach($otherStock->id);

        $this->actingAs($customerUser);

        $response = $this->postJson(route('storefront.validate-coupon'), [
            'code'      => $coupon->code,
            'branch_id' => $vendorBranch->id,
            'items'     => [
                ['stock_id' => $stocks[0]->id, 'total' => 100],
                ['stock_id' => $stocks[1]->id, 'total' => 100],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    });
});

describe('Coupon Customer Restrictions', function () {

    it('rejects coupon when customer is not in allowed list', function () {
        [$vendorAccount, $vendorBranch]                    = createVendorBranch();
        [$customerAccount, $customerBranch, $customerUser] = createAccount();
        [$otherAccount, $otherBranch]                      = createVendorBranch();

        $stocks = createStocksForBranch($vendorBranch->id, 1);

        $coupon = createCoupon($vendorBranch->id, [
            'type'             => 'amount',
            'value'            => 10,
            'min_order_amount' => 0,
        ]);

        // Attach only the other branch as allowed customer
        $coupon->customers()->attach($otherBranch->id);

        $this->actingAs($customerUser);

        $response = $this->postJson(route('storefront.validate-coupon'), [
            'code'      => $coupon->code,
            'branch_id' => $vendorBranch->id,
            'items'     => [
                ['stock_id' => $stocks[0]->id, 'total' => 100],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    });

    it('accepts coupon when customer is in allowed list', function () {
        [$vendorAccount, $vendorBranch]                    = createVendorBranch();
        [$customerAccount, $customerBranch, $customerUser] = createAccount();

        $stocks = createStocksForBranch($vendorBranch->id, 1);

        $coupon = createCoupon($vendorBranch->id, [
            'type'             => 'amount',
            'value'            => 10,
            'min_order_amount' => 0,
        ]);

        // Attach the customer branch as allowed
        $coupon->customers()->attach($customerBranch->id);

        $this->actingAs($customerUser);

        $response = $this->postJson(route('storefront.validate-coupon'), [
            'code'      => $coupon->code,
            'branch_id' => $vendorBranch->id,
            'items'     => [
                ['stock_id' => $stocks[0]->id, 'total' => 100],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    });
});
