<?php

require_once 'Helpers.php';

use function Tests\Feature\Http\Controllers\Account\Plan\Helpers\populateModule;
use function Tests\Feature\Http\Controllers\Account\Plan\Helpers\populateUser;

it(/**
 * @throws \Laravel\Octane\Exceptions\DdException
 */ 'can assign associate plan', function () {

    [$branch , $user] = populateUser();

    $this->actingAs($user);

    $associate = \App\Models\Plan::factory()->create([
        'title'      => 'Associate plan',
        'attributes' => populateModule(),
    ]);

    $trial = \App\Models\Plan::factory()->create([
        'plan_mode'          => 'trial',
        'associated_plan_id' => $associate->id,
        'attributes'         => populateModule(),
    ]);

    (new \App\Services\BranchPlanService)->assignCreate($branch, [$trial->id, $associate->id]);

    $subscriptionAssociate = $branch->plans()->where('plan_id', $associate->id)->first();

    expect($branch->plans()->count())->toBe(2)
        ->and($subscriptionAssociate->description)->toBe($associate['description'])
        ->and($subscriptionAssociate->amount)->toBe($associate['amount'])
        ->and($subscriptionAssociate->attributes)->toBe($associate['attributes'])
        ->and($subscriptionAssociate->attributes['name'])->toBe($associate['attributes']['name'])
        ->and($subscriptionAssociate->attributes['type'])->toBe($associate['attributes']['type'])
        ->and($subscriptionAssociate->attributes['attribute'])->toBe($associate['attributes']['attribute'])
        ->and($subscriptionAssociate->attributes['attribute'][0]['name'])->toBe($associate['attributes']['attribute'][0]['name'])
        ->and($subscriptionAssociate->attributes['attribute'][0]['value'])->toBe($associate['attributes']['attribute'][0]['value'])
        ->and($subscriptionAssociate->status)->toBe($associate['status'])
        ->and($subscriptionAssociate->duration)->toBe($associate['duration'])
        ->and($subscriptionAssociate->plan_type)->toBe($associate['plan_type']);

});
