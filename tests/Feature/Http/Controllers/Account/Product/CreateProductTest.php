<?php

namespace Http\Controllers\Account\Product;

require_once \Pest\testDirectory().'/Feature/util.php';

use App\Models\Branch;

use function Tests\Feature\createAccount;

beforeEach(function () {
    // Setup necessary models for existence checks
    [$account, $branch, $user] = createAccount();
    $this->user                = $user;
    $this->category            = \App\Models\Category::factory()->create();
    $this->categoryGroup       = \App\Models\CategoryGroup::factory()->create();
    $this->brand               = \App\Models\Brand::factory()->create([
        'owner_type' => Branch::class,
        'owner_id'   => $branch->id,
    ]);
});

function validData(array $overrides = []): array
{
    return array_merge([
        'category_id'       => 1,
        'category_group_id' => 1,
        'internal_id'       => 'INT-123',
        'sequence'          => 1,
        'brand_id'          => 1,
        'account_user'      => true,
        'name'              => 'Test Product',
        'images'            => ['image1.jpg', 'image2.jpg'],
        'description'       => 'Test description',
        'taxable'           => true,
        'country'           => 'US',
        'custom_data'       => [
            ['name' => 'color', 'value' => 'red', 'unit' => null],
            ['name' => 'size', 'value' => '10', 'unit' => 'cm'],
        ],
        'variants' => [
            [
                'images'      => ['variant1.jpg'],
                'name'        => 'Variant 1',
                'country'     => 'US',
                'barcode'     => '123456789',
                'description' => 'Variant description',
                'attributes'  => ['color' => 'red'],
                'packages'    => [
                    [
                        'name'     => 'Package 1',
                        'quantity' => 10,
                        'unit'     => 'pcs',
                    ],
                ],
                'measurements' => [
                    'height' => 10.5,
                    'width'  => 5.2,
                    'length' => 15.3,
                    'weight' => 2.1,
                ],
                'cargo_packages' => [
                    [
                        'name'     => 'Cargo Package',
                        'quantity' => 5,
                        'package'  => 'box',
                    ],
                ],
            ],
        ],
        'packaging' => 'Box',
    ], $overrides);
}

describe('testing validation', function () {

    it('validates product creation request rules', function ($errorField, $data) {

        $this->actingAs($this->user);
        $response = $this->post(route('account.products.store'), $data);
        $response->assertStatus(302);
        $response->assertSessionHasErrors([$errorField]);
    })->with([
        // Required fields
        //        'category_id is required' => [
        //            'errorField' => 'category_id',
        //            'data' => validData(['category_id' => null]),
        //        ],
        //        'name is required' => [
        //            'data' =>  validData(['name' => null]),
        //            'errorField' => 'name'
        //        ],
        //        'variants is required' => [
        //            'data' =>  validData(['variants' => null]),
        //            'errorField' => 'variants'
        //        ],
        //        'brand_id is required when account_user is true' => [
        //            'data' =>  validData(['brand_id' => null]),
        //            'errorField' => 'brand_id'
        //        ],
        //
        //        // Exists validation
        //        'category_id must exist' => [
        //            'data' =>  validData(['category_id' => 999]),
        //            'errorField' => 'category_id'
        //        ],
        //        'category_group_id must exist when provided' => [
        //            'data' =>  validData(['category_group_id' => 999]),
        //            'errorField' => 'category_group_id'
        //        ],
        //        'brand_id must exist' => [
        //            'data' =>  validData(['brand_id' => 999]),
        //            'errorField' => 'brand_id'
        //        ],
        //        'variant id must exist when provided' => [
        //            'data' =>  validData(['variants' => [['id' => 999]]]),
        //            'errorField' => 'variants.0.id'
        //        ],
        //
        //        // Array validation
        //        'images must be an array' => [
        //            'data' =>  validData(['images' => 'not-an-array']),
        //            'errorField' => 'images'
        //        ],
        //        'custom_data must be an array' => [
        //            'data' =>  validData(['custom_data' => 'not-an-array']),
        //            'errorField' => 'custom_data'
        //        ],
        //        'variants must be an array' => [
        //            'data' =>  validData(['variants' => 'not-an-array']),
        //            'errorField' => 'variants'
        //        ],
        //        'variants.*.packages must be an array' => [
        //            'data' =>  validData(['variants' => [['packages' => 'not-an-array']]]),
        //            'errorField' => 'variants.0.packages'
        //        ],
        //
        //        // Boolean validation
        //        'taxable must be boolean' => [
        //            'data' =>  validData(['taxable' => 'not-a-boolean']),
        //            'errorField' => 'taxable'
        //        ],
        //
        //        // Numeric validation
        //        'variants.*.packages.*.quantity must be numeric' => [
        //            'data' =>  validData(['variants' => [['packages' => [['quantity' => 'not-a-number']]]]]),
        //            'errorField' => 'variants.0.packages.0.quantity'
        //        ],
        //        'variants.*.measurements.height must be numeric' => [
        //            'data' =>  validData(['variants' => [['measurements' => ['height' => 'not-a-number']]]]),
        //            'errorField' => 'variants.0.measurements.height'
        //        ],
        //
        //        // Min validation
        //        'variants.*.packages.*.quantity must be at least 1' => [
        //            'data' =>  validData(['variants' => [['packages' => [['quantity' => 0]]]]]),
        //            'errorField' => 'variants.0.packages.0.quantity'
        //        ],
        //
        //        'variants.*.measurements.height must be at least 0' => [
        //            'data' =>  validData(['variants' => [['measurements' => ['height' => -1]]]]),
        //            'errorField' => 'variants.0.measurements.height'
        //        ],
        //
        //        // Required with validation
        //    'variants.*.cargo_packages.*.name is required with package' => [
        //        'data' => validData(['variants' => [['cargo_packages' => [['package' => 'box']]]]]),
        //        'errorField' => 'variants.0.cargo_packages.0.name'
        //    ],
        //
        //    'variants.*.cargo_packages.*.package is required with name' => [
        //    'data' => validData(['variants' => [['cargo_packages' => [['name' => 'Cargo']]]]]),
        //        'errorField' => 'variants.0.cargo_packages.0.package'
        //    ],
        //
        //    // Distinct validation
        'variants.*.barcode must be distinct' => [
            'data' => validData(['variants' => [
                ['barcode' => '123456789', 'name' => 'V1', 'packaging' => 'box', 'quantity' => 1],
                ['barcode' => '123456789', 'name' => 'V2', 'packaging' => 'box', 'quantity' => 1],
            ]]),
            'errorField' => 'variants.0.barcode',
        ],
    ]);
});
