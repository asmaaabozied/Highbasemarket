<?php

use App\Jobs\SendOrderNotificationsJob;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Stock;
use App\Models\User;
use App\Services\CalculateItemCommissionService;
use Mockery\Mock;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;
use function Tests\Feature\createAccount;

// Shared setup
beforeEach(function () {
    // Create seller and buyer branches
    [$_account, $branch, $_user] = createAccount();
    $branch->address             = [
        'country'  => 18,
        'state'    => 'test',
        'city'     => 'test',
        'street'   => 'test',
        'street12' => 'tst',
        'site'     => 'test',
        'building' => 'test',
    ];
    $branch->save();

    $this->sellerBranch = $branch;

    [$account2, $branch2, $buyerUser] = createAccount();

    $branch2->address = [
        'country'  => '',
        'state'    => '',
        'city'     => '',
        'street'   => '',
        'street12' => '',
        'site'     => '',
        'building' => '',
    ];
    $branch2->save();
    $this->buyerBranch = $branch2;

    // Link buyer as customer of seller with credit config
    $this->buyerBranch->myVendors()->create([
        'vendor_id' => $this->sellerBranch->id,
        'config'    => [
            'credit_settings' => [
                'number_of_bills'        => 5,
                'maximum_credit_limit'   => 1000.00,
                'credit_limit_per_order' => 300.00,
            ],
        ],
    ]);

    // Create product from seller
    $this->product = Stock::factory()->create([
        'branch_id'     => $this->sellerBranch->id,
        'quantity'      => 100,
        'moq'           => 1,
        'packaging'     => 'box',
        'selling_price' => 25.00,
        'allow_credit'  => true,
        'credit_limit'  => 500.00,
        'status'        => 'active',
        'show_price'    => true,
    ]);

    actingAs($buyerUser);
});

it('can create a credit order successfully', function () {

    $response = postJson(route('account.purchases.store'), [
        'payment_method' => 'credit',
        'products'       => [
            [
                'product_id' => $this->product->id,
                'packaging'  => 'box',
                'quantity'   => 4,
            ],
        ],
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('orders', [
        'branch_id'      => $this->buyerBranch->id,
        'employee_id'    => $this->buyerBranch->id,
        'payment_method' => 'credit',
        'total'          => 100.00,
    ]);

    $order = Order::first();

    $this->assertDatabaseHas('order_lines', [
        'order_id'   => $order->id,
        'product_id' => $this->product->id,
        'quantity'   => 4,
        'price'      => 25.00,
        'total'      => 100.00,
    ]);

    $line = $order->lines->first();
    expect($line->commission_amount_usd)->toBeFloat()->toBeGreaterThan(0);

    Queue::assertPushed(SendOrderNotificationsJob::class);
});

it('fails when stock is insufficient', function () {
    $this->product->update(['quantity' => 2]);

    $response = postJson('/api/purchases', [
        'payment_method' => 'credit',
        'products'       => [
            [
                'product_id' => $this->product->id,
                'packaging'  => 'box',
                'quantity'   => 5,
            ],
        ],
    ]);

    $response->assertUnprocessable();
    $response->assertInvalid('cart.0.item');
});

it('fails when quantity is below MOQ', function () {
    $this->product->update(['moq' => 10]);

    $response = postJson('/api/purchases', [
        'payment_method' => 'credit',
        'products'       => [
            [
                'product_id' => $this->product->id,
                'packaging'  => 'box',
                'quantity'   => 5,
            ],
        ],
    ]);

    $response->assertUnprocessable();
    $response->assertInvalid('cart.0.item');
});

it('fails when credit limit per order is exceeded', function () {
    $response = postJson('/api/purchases', [
        'payment_method' => 'credit',
        'products'       => [
            [
                'product_id' => $this->product->id,
                'packaging'  => 'box',
                'quantity'   => 13, // 13 * 25 = 325 > 300
            ],
        ],
    ]);

    $response->assertUnprocessable();
    $response->assertHasJsonValidationErrors(['error']);
    $this->assertStringContainsString('exceeds the allowed limit', $response->json('message'));
});

it('fails when total credit limit would be exceeded', function () {
    // Simulate existing order
    Order::factory()->create([
        'branch_id'      => $this->buyerBranch->id,
        'payment_method' => 'credit',
    ])->lines()->create([
        'product_id' => $this->product->id,
        'quantity'   => 1,
        'price'      => 25,
        'total'      => 25,
        'status'     => 'approved',
    ]);

    $response = postJson('/api/purchases', [
        'payment_method' => 'credit',
        'products'       => [
            [
                'product_id' => $this->product->id,
                'packaging'  => 'box',
                'quantity'   => 40, // 1000 + 25 > limit
            ],
        ],
    ]);

    $response->assertUnprocessable();
    $this->assertStringContainsString('exceeds the allowed credit limit', $response->json('message'));
});

it('rejects invalid payment method', function () {
    $response = postJson('/api/purchases', [
        'payment_method' => 'paypal',
        'products'       => [
            [
                'product_id' => $this->product->id,
                'packaging'  => 'box',
                'quantity'   => 2,
            ],
        ],
    ]);

    $response->assertUnprocessable();
    $response->assertInvalid('payment_method');
});

it('cannot order own product', function () {
    $ownBranch  = Branch::factory()->create(['account_id' => 999]);
    $ownProduct = Stock::factory()->create(['branch_id' => $ownBranch->id]);

    $user = User::factory()->create([
        'userable_id'   => $ownBranch->id,
        'userable_type' => Branch::class,
    ]);

    actingAs($user);

    $response = postJson('/api/purchases', [
        'payment_method' => 'credit',
        'products'       => [
            [
                'product_id' => $ownProduct->id,
                'packaging'  => 'box',
                'quantity'   => 2,
            ],
        ],
    ]);

    $response->assertUnprocessable();
    $response->assertJson([
        'message' => 'Can not order your own product',
    ]);
});

// ———————————————————————————————————
// 🛠️ Optional: Test Commission Fallback (when service fails)
// ———————————————————————————————————

it('uses 10% commission fallback when calculation fails', function () {
    Mock::mock(CalculateItemCommissionService::class)
        ->shouldReceive('make->process')
        ->andThrow(new Exception('Service failed'));

    $response = postJson('/api/purchases', [
        'payment_method' => 'credit',
        'products'       => [
            [
                'product_id' => $this->product->id,
                'packaging'  => 'box',
                'quantity'   => 10,
            ],
        ],
    ]);

    $response->assertOk();

    $order = Order::with('lines')->first();
    $line  = $order->lines->first();

    // Fallback: 10% of 250 = 25.00
    expect($line->commission_amount_usd)->toBe(25.00);
});
