<?php

require_once 'Helpers.php';

use App\Models\Product;

use function Tests\Feature\Http\Controllers\MessageSentController\Helpers\populateMessage;
use function Tests\Feature\Http\Controllers\MessageSentController\Helpers\populatePlan;
use function Tests\Feature\Http\Controllers\MessageSentController\Helpers\populateQuotable;
use function Tests\Feature\Http\Controllers\MessageSentController\Helpers\prepareData;

describe('message process', function () {

    it('can add customer to list', function ($type) {

        [
            $buyer,
            $seller,
            $seller_user,
            $buyer_user
        ] = prepareData(0);

        $seller_branch = $seller->branches()->first();

        $buyer_branch = $buyer->branches()->first();

        [$product, $brand] = populateQuotable();

        populatePlan($seller_branch);

        $quotable = [
            'id'   => $brand->id,
            'type' => \App\Models\Brand::class,
        ];

        if ($type === 'product') {
            $quotable = [
                'id'   => $product->id,
                'type' => Product::class,
            ];
        }

        $message = populateMessage(buyer: $buyer_branch, seller: $seller_branch, quotable: $quotable);

        $this->actingAs($seller_user)
            ->withSession(['current_branch', $seller_branch]);

        $response = $this->get(route('account.add-customers.show', $message->id));

        $new_seller = $seller->refresh()->branches()->first();

        $customer_list = $new_seller->customers();

        $response->assertSessionDoesntHaveErrors()
            ->assertStatus(302);

        expect($customer_list->count())->toBe(1)
            ->and($customer_list->first()->id)->toBe($buyer_branch->id)
            ->and($customer_list->first()->account_id)->toBe($buyer->id);

    })->with([
        ['type' => 'product'],
        ['type' => 'brand'],

    ]);

    it("can't add customer without plan subscription", function ($type) {

        [
            $buyer,
            $seller,
            $seller_user,
            $buyer_user
        ] = prepareData(0);

        $seller_branch = $seller->branches()->first();

        $buyer_branch = $buyer->branches()->first();

        [$product, $brand] = populateQuotable();

        $quotable = [
            'id'   => $brand->id,
            'type' => \App\Models\Brand::class,
        ];

        if ($type === 'product') {
            $quotable = [
                'id'   => $product->id,
                'type' => Product::class,
            ];
        }

        $message = populateMessage(buyer: $buyer_branch, seller: $seller_branch, quotable: $quotable);

        $this->actingAs($seller_user)
            ->withSession(['current_branch', $seller_branch]);

        $response = $this->get(route('account.add-customers.show', $message->id));

        $new_seller = $seller->refresh()->branches()->first();

        $customer_list = $new_seller->customers();

        $response->assertSessionHas('error')
            ->assertStatus(302);

        expect($customer_list->count())->toBe(0);

    })->with([
        ['type' => 'product'],
        ['type' => 'brand'],

    ]);

})->assignee('xmohamedamin');
