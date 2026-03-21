<?php

require_once 'Helpers.php';

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Tests\Feature\Http\Controllers\NewsController\Helpers\prapperData;

it('can create a news feed', function ($type) {

    Storage::fake('public');

    $user = prapperData(0);

    $product = \App\Models\Product::factory()->create();
    $brand   = \App\Models\Brand::factory()->create();

    $data = [
        'content'     => 'This My Blog news',
        'attachments' => [
            UploadedFile::fake()->image('create-image.png'),
            UploadedFile::fake()->image('create-image2.png'),
        ],
        'document' => UploadedFile::fake()->create('create-file.pdf'),
    ];

    $data['newsable_id']   = $brand->id;
    $data['newsable_type'] = \App\Models\Brand::class;

    if ($type === 'product') {
        $data['newsable_id']   = $product->id;
        $data['newsable_type'] = \App\Models\Product::class;
    }

    $this->ActingAs($user);

    $response = $this->post(route('account.news-feeds.store'), $data);

    $response->assertStatus(200);
    $response->assertSessionDoesntHaveErrors();

    $news = \App\Models\NewsFeed::query()->first();
    expect(\App\Models\NewsFeed::count())->toBe(1)
        ->and($news->content)->toBe($data['content']);

    Storage::disk('public')->assertExists(
        $news->getMedia('attachments')->first()->getPathRelativeToRoot()
    );

    Storage::disk('public')->assertExists(
        $news->getMedia('documents')->first()->getPathRelativeToRoot()
    );

})->with([
    ['type' => 'brand'],
    ['type' => 'product'],
])->assignee('xmohamedamin');

it('can validate rule', function ($type) {

    $user = prapperData(0);

    $product = \App\Models\Product::factory()->create();
    $brand   = \App\Models\Brand::factory()->create();

    $data = [
        'attachments' => 'file',
    ];

    $data['newsable_id']   = \App\Models\Brand::class;
    $data['newsable_type'] = $brand->id;

    if ($type === 'product') {
        $data['newsable_id']   = \App\Models\Product::class;
        $data['newsable_type'] = $product->id;
    }

    $this->ActingAs($user);

    $response = $this->post(route('account.news-feeds.store'), $data);

    $response->assertStatus(302);

    $news = \App\Models\NewsFeed::query()->first();
    expect(\App\Models\NewsFeed::count())->toBe(0)
        ->and($response->assertSessionHasErrors(
            ['content',
                'attachments',
                'newsable_id',
                'newsable_type',
            ]));

})->with([
    ['type' => 'brand'],
    ['type' => 'product'],
])->assignee('xmohamedamin');
