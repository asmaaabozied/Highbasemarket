<?php

require_once 'Helpers.php';

use function Tests\Feature\Http\Controllers\Admin\PlanController\Helpers\populateUpdateData;
use function Tests\Feature\Http\Controllers\Admin\PlanController\Helpers\prapperData;

it('can assign plan to branch', function ($data) {

    [$user , $countries , $branch] = prapperData();

    $this->actingAs($user);

    $updateData = populateUpdateData($data['title']);

    $plan = \App\Models\Plan::factory()->create($data);

    $subscription = \App\Models\Subscription::query()->create($plan->toArray());

    $branch->plans()->sync([$subscription->id]);

    $response = $this->put(route('admins.assign-plans.update', $subscription->id), $updateData);

    $response->assertStatus(302)
        ->assertSessionDoesntHaveErrors();

    $branch       = \App\Models\Branch::with('plans')->first();
    $subscription = $branch->plans()->first();

    expect($branch->plans()->count())->toBe(1)
        ->and($subscription->description)->toBe($data['description'])
        ->and($subscription->amount)->not->toBe($data['amount'])
        ->and($subscription->attributes)->not->toBe($data['attributes'])
        ->and($subscription->attributes['name'])->toBe($data['attributes']['name'])
        ->and($subscription->attributes['type'])->toBe($data['attributes']['type'])
        ->and($subscription->attributes['attribute'])->not->toBe($data['attributes']['attribute'])
        ->and($subscription->attributes['attribute'][0]['name'])->toBe($data['attributes']['attribute'][0]['name'])
        ->and($subscription->attributes['attribute'][0]['value'])->not->toBe($data['attributes']['attribute'][0]['value'])
        ->and($subscription->status)->toBe($data['status'])
        ->and($subscription->duration)->not->toBe($data['duration'])
        ->and($subscription->plan_type)->toBe($data['plan_type'])
        ->and($subscription->amount)->toBe($updateData['amount'])
        ->and($subscription->attributes)->toBe($updateData['attributes'])
        ->and($subscription->attributes['attribute'])->toBe($updateData['attributes']['attribute'])
        ->and($subscription->attributes['attribute'][0]['value'])->toBe($updateData['attributes']['attribute'][0]['value'])
        ->and($subscription->duration)->toBe($updateData['duration']);

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
    'Multi plan' => [
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
        ]],
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
