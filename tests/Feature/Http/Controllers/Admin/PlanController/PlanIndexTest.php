<?php

require_once 'Helpers.php';

use function Tests\Feature\Http\Controllers\Admin\PlanController\Helpers\prapperData;

it('can show a plan index', function () {
    [$user , $countries] = prapperData();

    $this->actingAs($user);

    $plan = \App\Models\Plan::factory()->create();

    $response = $this->get(route('admins.plans.index'));
    $response->assertSee($plan->title);

    $response->assertStatus(200);

})->assignee('xmohamedamin');
