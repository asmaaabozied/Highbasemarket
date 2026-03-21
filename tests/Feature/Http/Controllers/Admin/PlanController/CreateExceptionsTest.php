<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

require_once 'Helpers.php';

use function Tests\Feature\Http\Controllers\Admin\PlanController\Helpers\prapperData;

it('can create a exception for plan', function ($data) {

    [$user , $countries] = prapperData();

    $this->actingAs($user);

    $brand    = Brand::factory()->create();
    $product  = Product::factory()->create();
    $category = Category::factory()->create();

    $exceptionable = [
        [
            'name'               => $brand->name,
            'exceptionable_id'   => $brand->id,
            'exceptionable_type' => Brand::class,
        ],
        [
            'name'               => $product->name,
            'exceptionable_id'   => $product->id,
            'exceptionable_type' => Product::class,
        ],
        [
            'name'               => $category->name,
            'exceptionable_id'   => $category->id,
            'exceptionable_type' => Category::class,
        ],
    ];

    $select_exception = $exceptionable[array_rand($exceptionable)];
    $current          = [
        'name'               => $select_exception['name'],
        'exceptionable_id'   => $select_exception['exceptionable_id'],
        'exceptionable_type' => $select_exception['exceptionable_type'],
    ];

    $exception = ['exceptions' => [[
        'exccptionables' => [$current],
        'attributes'     => $data['attributes'],
    ]]];

    $response = $this->post(route('api.exceptions.store'), $exception);

    $json = $response->json();

    $data['exceptions'] = $json['data'];

    $response1 = $this->post(route('admins.plans.store'), $data);

    $response1->assertStatus(302)
        ->assertSessionDoesntHaveErrors();

    $plan       = \App\Models\Plan::first();
    $exceptions = $plan->exceptions()->with('attributes')->first();

    expect($plan->title)->toBe($data['title'])
        ->and(\App\Models\Plan::count())->toBe(1)
        ->and($plan->description)->toBe($data['description'])
        ->and($plan->amount)->toBe($data['amount'])
        ->and($plan->attributes)->toBe($data['attributes'])
        ->and($plan->status)->toBe($data['status'])
        ->and($plan->duration)->toBe($data['duration'])
        ->and($plan->plan_type)->toBe($data['plan_type'])
        ->and($plan->exceptions()->count())->toBe(1)
        ->and($exceptions->attributes()->count())->toBe(1)
        ->and($exceptions->exceptionable_id)->toBe($current['exceptionable_id'])
        ->and($exceptions->exceptionable_type)->toBe($current['exceptionable_type'])
        ->and($exceptions->attributes()->first()->attributes)->toBe($exception['exceptions'][0]['attributes']);

})->with([
    'Service plan' => [
        [
            'title'       => 'Plan-Service',
            'description' => 'lorem ipsum',
            'amount'      => 200,
            'attributes'  => [
                'name'      => 'Customers',
                'type'      => 'services',
                'attribute' => [
                    ['name' => 'allow', 'type' => 'select', 'value' => true, 'options' => [
                        ['option' => true],
                        ['option' => false],
                    ]],
                ],
                'status' => 1,
            ],
            'status'            => 'active',
            'duration'          => 45,
            'plan_type'         => 'globalMarket',
            'plan_mode'         => 'paid',
            'is_auto_renewable' => true,
        ],
    ],
    'Local plan' => [
        [
            'title'       => 'Plan-local',
            'description' => 'lorem ipsum',
            'amount'      => 100,
            'attributes'  => [
                'name'      => 'Order',
                'type'      => 'localMarket',
                'attribute' => [
                    [
                        'name'  => 'commission_amount',
                        'type'  => 'text',
                        'value' => 20,
                    ],
                    [
                        'name'  => 'is_percentage',
                        'type'  => 'checkbox',
                        'value' => false,
                    ],
                ],
                'status' => 1,
            ],
            'status'            => 'active',
            'duration'          => 45,
            'plan_type'         => 'globalMarket',
            'plan_mode'         => 'paid',
            'is_auto_renewable' => true,
        ],
    ],
    'Global Market' => [
        [
            'title'       => 'Plan-Global',
            'description' => 'lorem ipsum',
            'amount'      => 50,
            'attributes'  => [
                'name'      => 'Add Customer',
                'type'      => 'globalMarket',
                'attribute' => [
                    [
                        'name'  => 'numberOfRequests',
                        'type'  => 'text',
                        'value' => 2,
                    ],
                    [
                        'name'  => 'amountPerRequest',
                        'type'  => 'text',
                        'value' => 1,
                    ],
                    [
                        'name'  => 'is_percentage',
                        'type'  => 'checkbox',
                        'value' => false,
                    ],
                ],
            ],
            'status'            => 'active',
            'duration'          => 45,
            'plan_type'         => 'globalMarket',
            'plan_mode'         => 'paid',
            'is_auto_renewable' => true,
        ],
    ],
]);

it('can validate rule', function ($field) {

    [$user , $countries] = prapperData();

    $this->actingAs($user);

    $exception = ['exceptions' => [[
        'exccptionables' => 'excable',
        'attributes'     => 'attributes',
    ]]];

    $fields = [];

    if ($field === 'exceptions') {
        $exception = \Illuminate\Support\Arr::except($exception, 'exceptions');

        $fields[] = $field;
    }

    foreach ($exception['exceptions'] ?? [] as $key => $excp) {
        $fields[] = "exceptions.$key.$field";

        $exception['exceptions'][] = \Illuminate\Support\Arr::except($excp, $field);

    }

    $response = $this->post(route('api.exceptions.store'), $exception);

    $response->assertStatus(302)
        ->assertSessionHasErrors($fields);

})->with([
    ['field' => 'exccptionables'],
    ['field' => 'attributes'],
    ['field' => 'exceptions'],
]);
