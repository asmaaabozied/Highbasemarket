<?php

use App\Models\Branch;
use App\Models\Brand;

require_once 'Helpers.php';

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Tests\Feature\Http\Controllers\BrandController\Helpers\createPermissions;
use function Tests\Feature\Http\Controllers\BrandController\Helpers\populateFields;
use function Tests\Feature\Http\Controllers\BrandController\Helpers\prapperData;

it('can update a brand', function (array $data) {
    [$account , $branch, $user] = prapperData();

    createPermissions($user, ['update brand']);

    $this->actingAs($user);

    Storage::fake('local');

    $file = UploadedFile::fake()->image('created-logo.png', 640, 480);

    $logo = $this->post(route('chunk-upload'), [
        'attach' => $file,
    ]);

    $collection = $this->post(route('chunk-upload'), [
        'attach' => $file,
    ]);

    $branch_id = $account->branches()->where('id', '<>', $branch->id)->first()->id;

    $brand = Brand::factory()->create([
        'owner_type' => Branch::class,
        'owner_id'   => $branch->id,
    ]);

    $data['logo']             = $logo->getContent();
    $data['collection_image'] = $collection->getContent();
    $data['owner_type']       = Branch::class;
    $data['owner_id']         = $branch_id;

    if ($data['ownership_type'] === 'distributor') {
        $data['owner_type'] = '';
        $data['owner_id']   = null;
    }

    $response = $this->put(route('brands.update', $brand->id), $data);

    $brand->refresh();

    $response->assertSessionHasNoErrors();

    expect($response->status())->toBe(302)
        ->and($brand->name)->toBe($data['name'])
        ->and($brand->description)->toBe($data['description'])
        ->and($brand->ownership_type)->toBe($data['ownership_type'])
        ->and(['owner', 'distributor'])->toContain($brand->ownership_type);

    Storage::disk('public')->assertExists(
        $brand->getMedia('brands')->first()->getPathRelativeToRoot()
    );

})
    ->with([
        'Full field data' => [
            ['name'              => 'Brand New',
                'description'    => 'Brand New Description',
                'ownership_type' => 'owner',
            ],
        ],
        'another test' => [[
            'name'           => 'AlIeen',
            'description'    => 'Brand New Description',
            'ownership_type' => 'distributor',
        ]],
        'distributor' => [[
            'name'           => 'Brand New',
            'description'    => 'Brand New Description',
            'ownership_type' => 'distributor',
        ]],

    ])
    ->assignee('xmohamedamin');

it('can validate rules', function ($field) {

    [$account , $employees, $user] = prapperData();

    [$data, $fields] = populateFields($field, 'update');

    createPermissions($user, ['update brand']);

    $this->actingAs($user);

    $branch_id = $account->branches()->first()->id;

    $brand = Brand::factory()->create([
        'owner_type' => Branch::class,
        'owner_id'   => $branch_id,
    ]);

    $response = $this->put(route('brands.update', $brand->id), $data);

    expect($response->status())->toBe(302)
        ->and($response->assertSessionHasErrors($fields));
})
    ->with([
        ['field' => 'name'],
    ])
    ->assignee('xmohamedamin');

it('can validate permission', function ($data) {

    [$account , $employees, $user] = prapperData();

    $this->actingAs($user);

    Storage::fake('local');

    $file = UploadedFile::fake()->image('created-logo.png', 640, 480);

    $logo = $this->post(route('chunk-upload'), [
        'attach' => $file,
    ]);

    $collection = $this->post(route('chunk-upload'), [
        'attach' => $file,
    ]);

    $branch_id = $account->branches()->first()->id;

    $data['logo']             = $logo->getContent();
    $data['collection_image'] = $collection->getContent();
    $data['owner_type']       = Branch::class;
    $data['owner_id']         = $branch_id;

    $brand = Brand::factory()->create([
        'owner_type' => Branch::class,
        'owner_id'   => $branch_id,
    ]);

    $response = $this->put(route('brands.update', $brand->id), $data);

    $brand->refresh();

    expect($response->status())->toBe(403);
})
    ->with([
        'Full field data' => [
            [
                'name'           => 'Brand New',
                'description'    => 'Brand New Description',
                'ownership_type' => 'owner',
            ],
        ],
    ])
    ->assignee('xmohamedamin');
