<?php

use App\Models\Branch;
use App\Models\Brand;

require_once 'Helpers.php';

use App\Models\BrandDistributor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Tests\Feature\Http\Controllers\BrandController\Helpers\createPermissions;
use function Tests\Feature\Http\Controllers\BrandController\Helpers\populateFields;
use function Tests\Feature\Http\Controllers\BrandController\Helpers\prapperData;

it('can request claims when a brand is select different branch', function (array $data) {
    [$account , $branch, $user] = prapperData();

    createPermissions($user, ['create brand']);

    $this->actingAs($user);

    $branch_id = $account->branches()->where('id', '<>', $branch->id)->first()->id;

    $selected_brand = Brand::factory()->create([
        'owner_type' => Branch::class,
        'owner_id'   => $branch_id,
    ]);

    Storage::fake('local');

    $file = UploadedFile::fake()->image('created-logo.png', 640, 480);

    $logo = $this->post(route('chunk-upload'), [
        'attach' => $file,
    ]);

    $collection = $this->post(route('chunk-upload'), [
        'attach' => $file,
    ]);

    $request = collect($data)->map(function ($item) use ($logo, $collection, $selected_brand) {
        $item['logo']             = $logo->getContent();
        $item['collection_image'] = $collection->getContent();
        $item['brand_id']         = $selected_brand->id;

        return $item;

    })->toArray();

    $response = $this->post('brands', ['brands' => $request]);

    $brand = Brand::query()->first();

    foreach ($request as $key => $req) {
        if ($req['ownership_type'] === 'owner') {
            expect($response->assertSessionHas(['claim_request']));
        } else {

            $brand_distributor = BrandDistributor::query()->first();
            expect([$selected_brand->id])->toContain($brand_distributor->brand_id)
                ->and($brand_distributor->distributor_id)->toBe($branch->id);

        }
    }

})
    ->with([
        'Full field data' => [[
            ['name'              => 'Brand New',
                'description'    => 'Brand New Description',
                'ownership_type' => 'owner',
            ],
        ]],
        'another test' => [[[
            'name'           => 'AlIeen',
            'description'    => 'Brand New Description',
            'ownership_type' => 'distributor',
        ]]],
        'distributor' => [[[
            'name'           => 'Brand New',
            'description'    => 'Brand New Description',
            'ownership_type' => 'distributor',
        ]]],

    ])
    ->assignee('xmohamedamin');

it('can not create a brand with owned brand', function (array $data) {
    [$account , $branch, $user] = prapperData();

    createPermissions($user, ['create brand']);

    $this->actingAs($user);

    $branch_id = $account->branches()->where('id', '<>', $branch->id)->first()->id;

    $current_brand = Brand::factory()->create([
        'owner_type' => Branch::class,
        'owner_id'   => $branch->id,
        ...$data[0],
    ]);

    $selected_brand = Brand::factory()->create([
        'owner_type' => Branch::class,
        'owner_id'   => $branch_id,
    ]);

    Storage::fake('local');

    $file = UploadedFile::fake()->image('created-logo.png', 640, 480);

    $logo = $this->post(route('chunk-upload'), [
        'attach' => $file,
    ]);

    $collection = $this->post(route('chunk-upload'), [
        'attach' => $file,
    ]);

    $request = collect($data)->map(function ($item) use ($logo, $collection, $current_brand) {
        $item['logo']             = $logo->getContent();
        $item['collection_image'] = $collection->getContent();
        $item['brand_id']         = $current_brand->id;

        return $item;

    })->toArray();

    $response = $this->post('brands', ['brands' => $request]);

    expect($response->assertSessionHas(['error']));

})
    ->with([
        'Full field data' => [[
            ['name'              => 'Brand New',
                'description'    => 'Brand New Description',
                'ownership_type' => 'owner',
            ],
        ]],
        'another test' => [[[
            'name'           => 'AlIeen',
            'description'    => 'Brand New Description',
            'ownership_type' => 'distributor',
        ]]],
        'distributor' => [[[
            'name'           => 'Brand New',
            'description'    => 'Brand New Description',
            'ownership_type' => 'distributor',
        ]]],

    ])
    ->assignee('xmohamedamin');

it('can validate rules', function ($field) {

    [$account , $employees, $user] = prapperData();

    [$data, $fields] = populateFields($field);

    createPermissions($user, ['create brand']);

    $this->actingAs($user);

    $response = $this->post('brands', ['brands' => $data]);

    expect(\App\Models\Brand::count())->toBe(0)
        ->and($response->status())->toBe(302)
        ->and($response->assertSessionHasErrors($fields));
})
    ->with([
        ['field' => 'name'],
        ['field' => 'logo'],
        ['field' => 'ownership_type'],
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

    $request = collect($data)->map(function ($item) use ($logo, $collection) {
        $item['logo']             = $logo->getContent();
        $item['collection_image'] = $collection->getContent();

        return $item;

    })->toArray();

    $response = $this->post('brands', ['brands' => $request]);

    $brand = Brand::query()->first();

    expect($response->status())->toBe(403)
        ->and(Brand::count())->toBe(0);
})
    ->with([
        'Full field data' => [[
            ['name'              => 'Brand New',
                'description'    => 'Brand New Description',
                'ownership_type' => 'owner',
            ],
        ]],
    ])
    ->assignee('xmohamedamin');
