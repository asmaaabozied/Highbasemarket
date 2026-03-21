<?php

use App\Models\Brand;

require_once 'Helpers.php';

use App\Models\BrandDistributor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Tests\Feature\Http\Controllers\BrandController\Helpers\createPermissions;
use function Tests\Feature\Http\Controllers\BrandController\Helpers\populateFields;
use function Tests\Feature\Http\Controllers\BrandController\Helpers\prapperData;

it('can create a brand', function (array $data) {
    [$account , $branch, $user] = prapperData();

    createPermissions($user, ['create brand']);

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

    $response->assertSessionHasNoErrors();

    expect($response->status())->toBe(302)
        ->and(Brand::count())->toBe(1);

    foreach ($request as $req) {
        expect($brand->name)->toBe($req['name'])
            ->and($brand->description)->toBe($req['description'])
            ->and($brand->ownership_type)->toBe($req['ownership_type'])
            ->and(['owner', 'distributor'])->toContain($brand->ownership_type);

        if ($req['ownership_type'] === 'distributor') {

            $brand_distributor = BrandDistributor::query()->first();
            expect([$brand->id])->toContain($brand_distributor->brand_id)
                ->and($brand_distributor->distributor_id)->toBe($branch->id);
        }
    }

    Storage::disk('public')->assertExists(
        $brand->getMedia('brands')->first()->getPathRelativeToRoot()
    );

})
    ->with([
        'Full field data' => [[
            ['name'              => 'Brand New',
                'brand_id'       => null,
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
