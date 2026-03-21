<?php

/**
 * Unit Tests for ValidateCartCouponsAction
 *
 * This file contains tests for the ValidateCartCouponsAction execution.
 */

use App\Actions\Orders\ValidateCartCouponsAction;
use App\Models\Branch;
use App\Models\Coupon;
use Illuminate\Validation\ValidationException;

describe('ValidateCartCouponsAction', function () {

    it('validates and calculates discounts successfully', function () {
        $buyerBranch  = Branch::factory()->create();
        $sellerBranch = Branch::factory()->create();

        // Create a coupon for the seller branch
        $coupon = Coupon::factory()->amount()->create([
            'branch_id' => $sellerBranch->id,
            'value'     => 10,
        ]);

        // Mock lines (cart items)
        $lines = collect([
            (object) [
                'product'    => (object) ['branch_id' => $sellerBranch->id],
                'product_id' => 101, // stock_id
                'total'      => 100.0,
            ],
            (object) [
                'product'    => (object) ['branch_id' => $sellerBranch->id],
                'product_id' => 102, // stock_id
                'total'      => 50.0,
            ],
        ]);

        $input = [
            ['coupon_id' => $coupon->id],
        ];

        $action = new ValidateCartCouponsAction;
        $result = $action->execute($input, $lines, $buyerBranch);

        expect($result)->toHaveCount(1);
        expect($result->first()['coupon_id'])->toBe($coupon->id);
        expect($result->first()['discount_amount'])->toBe(10.0);
    });

    it('throws exception if coupon does not exist', function () {
        $lines = collect([]);
        $input = [
            ['coupon_id' => 99999],
        ];

        $action = new ValidateCartCouponsAction;

        $action->execute($input, $lines);
    })->throws(ValidationException::class);

    it('throws exception if coupon branch does not match any item branch', function () {
        $sellerBranch1 = Branch::factory()->create();
        $sellerBranch2 = Branch::factory()->create();

        $coupon = Coupon::factory()->create([
            'branch_id' => $sellerBranch1->id,
            'code'      => 'B1_COUPON',
        ]);

        // Cart items only from sellerBranch2
        $lines = collect([
            (object) [
                'product'    => (object) ['branch_id' => $sellerBranch2->id],
                'product_id' => 201,
                'total'      => 100.0,
            ],
        ]);

        $input = [
            ['coupon_id' => $coupon->id],
        ];

        $action = new ValidateCartCouponsAction;

        $action->execute($input, $lines);
    })->throws(ValidationException::class);

    it('calculates percentage discounts correctly', function () {
        $sellerBranch = Branch::factory()->create();

        $coupon = Coupon::factory()->percent()->create([
            'branch_id' => $sellerBranch->id,
            'value'     => 20, // 20%
        ]);

        $lines = collect([
            (object) [
                'product'    => (object) ['branch_id' => $sellerBranch->id],
                'product_id' => 101,
                'total'      => 100.0,
            ],
        ]);

        $input = [
            ['coupon_id' => $coupon->id],
        ];

        $action = new ValidateCartCouponsAction;
        $result = $action->execute($input, $lines);

        expect($result->first()['discount_amount'])->toBe(20.0);
    });

    it('handles multiple sellers with separate coupons', function () {
        $seller1 = Branch::factory()->create();
        $seller2 = Branch::factory()->create();

        $coupon1 = Coupon::factory()->amount()->create(['branch_id' => $seller1->id, 'value' => 5, 'min_order_amount' => 0]);
        $coupon2 = Coupon::factory()->amount()->create(['branch_id' => $seller2->id, 'value' => 15, 'min_order_amount' => 0]);

        $lines = collect([
            (object) ['product' => (object) ['branch_id' => $seller1->id], 'product_id' => 1, 'total' => 50.0],
            (object) ['product' => (object) ['branch_id' => $seller2->id], 'product_id' => 2, 'total' => 100.0],
        ]);

        $input = [
            ['coupon_id' => $coupon1->id],
            ['coupon_id' => $coupon2->id],
        ];

        $action = new ValidateCartCouponsAction;
        $result = $action->execute($input, $lines);

        expect($result)->toHaveCount(2);
        expect($result->where('coupon_id', $coupon1->id)->first()['discount_amount'])->toBe(5.0);
        expect($result->where('coupon_id', $coupon2->id)->first()['discount_amount'])->toBe(15.0);
    });
});
