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

    $plan = \App\Models\Plan::factory()->create();

    $attribute = \App\Models\ExceptionAttribute::factory()
        ->withExceptionables(2, $select_exception['exceptionable_id'], $select_exception['exceptionable_type'], $plan->id)
        ->create();

    $attributes = (new \App\Services\PlanExceptionService)->getExceptionsByPlanId($plan->id);

    $current = [
        'exceptionable_id'   => $select_exception['exceptionable_id'],
        'exceptionable_type' => $select_exception['exceptionable_type'],
    ];

    $updateExceptionData = $attributes->map(function ($item) use ($data) {
        return [
            'exccptionables' => $item['exccptionables'],
            'attributes'     => $data['attributes'],
            'attribute_id'   => $item['attribute_id'],
        ];
    })->toArray();

    $response = $this->put(route('admins.exceptions.update', $plan), ['exceptions' => $updateExceptionData]);

    $response->assertStatus(302)
        ->assertSessionDoesntHaveErrors();

    $plan       = \App\Models\Plan::first();
    $exceptions = $plan->exceptions()->with('attributes')->first();

    expect($plan->exceptions()->count())->toBe(2)
        ->and($exceptions->attributes()->count())->toBe(1)
        ->and($exceptions->exceptionable_id)->toBe($current['exceptionable_id'])
        ->and($exceptions->exceptionable_type)->toBe($current['exceptionable_type'])
        ->and($exceptions->attributes()->first()->attributes)->toBe($updateExceptionData[0]['attributes']);

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
                        'name'  => 'first_commission_amount',
                        'type'  => 'text',
                        'value' => 20,
                    ],
                    [
                        'name'  => 'first_is_percentage',
                        'type'  => 'checkbox',
                        'value' => false,
                    ],
                    [
                        'name'  => 'second_commission_amount',
                        'type'  => 'text',
                        'value' => 20,
                    ],
                    [
                        'name'  => 'second_is_percentage',
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
