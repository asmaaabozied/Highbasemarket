<?php

require_once 'Helpers.php';

use Illuminate\Support\Facades\Storage;

use function Tests\Feature\Http\Controllers\SupportController\Helpers\prapperData;

it('can send an issue', function () {

    $user = prapperData(0);

    Storage::fake('public');

    $data = [
        'title'   => 'My new Title',
        'email'   => 'text@example.com',
        'type'    => 'My new Type',
        'content' => 'This My Blog news',
        'file'    => \Illuminate\Http\UploadedFile::fake()->image('file.png'),
    ];

    $this->actingAs($user);

    $response = $this->post(route('supports.store', $data));

    $response->assertStatus(302)
        ->assertSessionDoesntHaveErrors();

    $support = \App\Models\Support::query()->first();

    expect(\App\Models\Support::count())->toBe(1)
        ->and($support->title)->toBe($data['title'])
        ->and($support->email)->toBe($data['email'])
        ->and($support->type)->toBe($data['type'])
        ->and($support->content)->toBe($data['content']);

})->assignee('xmohamedamin');

it('can validate rules', function ($field) {

    Storage::fake('public');

    $user = prapperData(0);

    $data = [
        'title'   => 'My new Title',
        'email'   => 'text@example.com',
        'type'    => 'My new Type',
        'content' => 'This My Blog news',
        'file'    => \Illuminate\Http\UploadedFile::fake()->image('file.png'),
    ];

    $this->actingAs($user);

    $response = $this->post(route('supports.store'), \Illuminate\Support\Arr::except($data, $field));
    $response->assertStatus(302);

    expect(\App\Models\Support::count())->toBe(0)
        ->and($response->assertSessionHasErrors(
            [$field]));

})->with([
    ['field' => 'title'],
    ['field' => 'email'],
    ['field' => 'type'],
    ['field' => 'content'],
])->assignee('xmohamedamin');
