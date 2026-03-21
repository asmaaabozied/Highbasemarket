<?php

use App\Models\Brand;

require_once 'Helpers.php';

use function Tests\Feature\Http\Controllers\ClaimController\Helpers\prapperData;

it('can request claim', function () {

    $user = prapperData(0);

    $this->actingAs($user);

    $claimable = Brand::factory()->create();

    $data = [
        'claimable_id'   => $claimable->id,
        'claimable_type' => Brand::class,
        'status'         => 'pending',
        'config'         => [[
            'type' => 'Brand owner',
        ]],
    ];

    $response = $this->post(route('account.claims.store'), $data);
    $response->assertStatus(302)
        ->assertSessionDoesntHaveErrors();

    $claim = \App\Models\Claim::first();

    expect(\App\Models\Claim::count())->toBe(1)
        ->and($claim->claimable_id)->toBe($data['claimable_id'])
        ->and($claim->claimable_type)->toBe($data['claimable_type'])
        ->and($claim->status)->toBe($data['status'])
        ->and($claim->config)->toBe($data['config']);

});

it('can validate  claim rules', function ($field) {

    $user = prapperData(0);

    $this->actingAs($user);

    $claimable = Brand::factory()->create();

    $data = [
        'claimable_id'   => 'claimable',
        'claimable_type' => 20893,
        'status'         => 1234555,
        'config'         => 'type , brand',
    ];

    $response = $this->post(route('account.claims.store'), \Illuminate\Support\Arr::except($data, $field));
    $response->assertStatus(302)
        ->assertSessionHasErrors([
            'claimable_id',
            'claimable_type',
            'status',
            'config',
        ]);

})->with([
    ['field' => 'claimable_id'],
    ['field' => 'claimable_type'],
    ['field' => 'status'],
]);
