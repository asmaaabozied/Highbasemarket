<?php

require_once 'Helpers.php';

use Illuminate\Http\UploadedFile;

use function Tests\Feature\Http\Controllers\RFQController\Helpers\fake_file_envirenment;
use function Tests\Feature\Http\Controllers\RFQController\Helpers\prapperData;

it('can post rfq', function (array $data) {

    fake_file_envirenment();

    [$branch , $user , $countries , $group] = prapperData(0);

    $data['branch_id']           = $branch->id;
    $data['preferred_countries'] = $countries->toArray();
    $data['category_id']         = $group->category_id;
    $data['category_group_id']   = $group->id;

    $this->actingAs($user);

    $response = $this->post(route('account.quote-rfq.store'), $data);

    $response->assertStatus(302)
        ->assertSessionDoesntHaveErrors();

    $rfq = \App\Models\RfqPost::query()->first();

    expect($rfq->branch_id)->toBe($data['branch_id'])
        ->and($rfq->preferred_countries)->toBe($data['preferred_countries'])
        ->and($rfq->category_id)->toBe($data['category_id'])
        ->and($rfq->category_group_id)->toBe($data['category_group_id'])
        ->and($rfq->name)->toBe($data['name'])
        ->and($rfq->published)->toBe($data['published'])
        ->and($rfq->address)->toBe($data['address'])
        ->and($rfq->description)->toBe($data['description']);

})->with([
    'Post rfq' => [
        [
            'name'      => 'My branch',
            'published' => 1,
            'address'   => [
                ['state' => '1992', 'country' => '18'],
            ],
            'description' => 'This is a test RFQ',
            'attachment'  => UploadedFile::fake()->image('rfq_attachment.jpg'),
        ]],

    'Draft rfq' => [
        [
            'name'      => 'Branch 2',
            'ended_at'  => '',
            'published' => 0,
            'address'   => [
                ['state' => '1992', 'country' => '18'],
            ],
            'description' => 'This is a test RFQ',
        ]],

    'Limited rfq' => [[
        'name'      => 'Branch 3',
        'published' => 0,
        'ended_at'  => \Carbon\Carbon::now()->addMonth(),
        'address'   => [
            ['state' => '1992', 'country' => '18'],
        ],
        'description' => 'This is a test RFQ',
    ]],
])
    ->assignee('xmohamedamin');

it('can validate rules', function ($field) {

    [$branch , $user , $countries , $group] = prapperData(0);

    $data = [
        'name'      => 'Branch 2',
        'ended_at'  => '',
        'published' => 0,
        'address'   => [
            ['state' => '1992', 'country' => '18'],
        ],
        'description'         => 'This is a test RFQ',
        'branch_id'           => $branch->id,
        'preferred_countries' => 'sudan , bahrain , egypt',
        'category_id'         => $group->category_id,
        'category_group_id'   => $group->id,
    ];

    $data = \Illuminate\Support\Arr::except($data, $field);

    $this->actingAs($user);

    $response = $this->post(route('account.quote-rfq.store'), $data);

    $response->assertStatus(302)
        ->assertSessionHasErrors([$field, 'preferred_countries']);

    expect(\App\Models\RfqPost::count())->toBe(0);

})->with([
    ['field' => 'name'],
    ['field' => 'branch_id'],
    ['field' => 'description'],
    ['field' => 'category_id'],
]);
