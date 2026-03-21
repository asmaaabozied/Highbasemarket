<?php

require_once 'Helpers.php';

use function Tests\Feature\Http\Controllers\RFQController\Helpers\prapperData;

it('can show a RFQ', function () {
    [$branch,
        $user,
        $countries,
        $group] = prapperData();

    $this->actingAs($user);

    $brand = \App\Models\RfqPost::factory()->create([
        'branch_id'         => $branch->id,
        'category_id'       => $group->category_id,
        'category_group_id' => $group->id,
    ]);

    $response = $this->get('/storefront/global?type=rfqs');

    $response->assertStatus(200);

})->assignee('xmohamedamin');
