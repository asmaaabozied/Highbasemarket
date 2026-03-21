<?php

require_once 'Helpers.php';

use function Tests\Feature\Http\Controllers\Admin\PlanController\Helpers\populateFields;
use function Tests\Feature\Http\Controllers\Admin\PlanController\Helpers\prapperData;

it('can update a new plan ', function ($data) {

    [$user , $countries] = prapperData();

    $plan = \App\Models\Plan::factory()->create();

    $this->actingAs($user);

    $response = $this->put(route('admins.plans.update', $plan->id), $data);

    $response->assertStatus(302)
        ->assertSessionDoesntHaveErrors();

    $plan = \App\Models\Plan::first();

    expect($plan->title)->toBe($data['title'])
        ->and(\App\Models\Plan::count())->toBe(1)
        ->and($plan->description)->toBe($data['description'])
        ->and($plan->amount)->toBe($data['amount'])
        ->and($plan->attributes)->toBe($data['attributes'])
        ->and($plan->status)->toBe($data['status'])
        ->and($plan->duration)->toBe($data['duration'])
        ->and($plan->plan_type)->toBe($data['plan_type']);

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

    [$data , $fields] = populateFields($field);

    [$user , $countries] = prapperData();

    $plan = \App\Models\Plan::factory()->create();

    $this->actingAs($user);

    $response = $this->put(route('admins.plans.update', $plan->id), $data);

    $response->assertStatus(302)
        ->assertSessionHasErrors($fields);

})->with([
    ['field' => 'title'],
    ['field' => 'amount'],
    ['field' => 'attributes'],
]);
