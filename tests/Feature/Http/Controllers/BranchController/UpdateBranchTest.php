<?php

require_once 'Helpers.php';

use Illuminate\Support\Facades\Storage;

use function Tests\Feature\Http\Controllers\BranchController\Helpers\createPermissions;
use function Tests\Feature\Http\Controllers\BranchController\Helpers\fake_file_envirenment;
use function Tests\Feature\Http\Controllers\BranchController\Helpers\populateFields;
use function Tests\Feature\Http\Controllers\BranchController\Helpers\populateFiles;
use function Tests\Feature\Http\Controllers\BranchController\Helpers\prapperData;

describe('update branch', function () {

    it('can update a branch', function () {

        fake_file_envirenment();

        [$account , $branch , $user] = prapperData();

        createPermissions($user, ['update branch']);

        $this->actingAs($user);

        $data = [
            'name'        => 'update Name',
            'description' => 'update description',
            'cr'          => 'update Name',
            'tax_number'  => '12344',
            'cr_image'    => populateFiles(),
            'cover_image' => populateFiles(),
            'image'       => populateFiles(),
            'account_id'  => $account->id,
            'phone'       => ['code' => '232', 'number' => '2322222'],
            'address'     => [
                'country' => '1234',
                'state'   => '1234',
                'city'    => '1234',
                'street'  => '1234',
            ],
            'config' => [
                'working_days' => [
                    'sunday',
                    'monday',
                    'tuesday',
                ],
                'number_of_shifts'    => 2,
                'shift_working_hours' => [
                    [
                        'end' => '17:00', 'start' => '08:00',
                    ],
                ],
                'enable_global_profile' => true,
                'enable_local_profile'  => false,
            ],
            'addresses' => [
                [
                    'address_name'       => 'test name',
                    'address_operations' => 'receiver',
                    'address_purpose'    => 'warehouse',
                    'address'            => [
                        'country'      => '1234',
                        'state'        => '1234',
                        'city'         => '1234',
                        'street'       => '1234',
                        'pin_location' => ['lan' => 123232, 'lat' => 1224444],
                    ],
                    'employee' => [
                        'employee_id'     => 1,
                        'releasing_stock' => true,
                        'receiving_stock' => false,
                    ],
                ],
                [
                    'address_name'       => 'test name',
                    'address_operations' => 'receiver',
                    'address_purpose'    => 'warehouse',
                    'address'            => [
                        'country'      => '1234',
                        'state'        => '1234',
                        'city'         => '1234',
                        'street'       => '1234',
                        'pin_location' => ['lan' => 123232, 'lat' => 1224444],
                    ],
                    'employee' => [
                        'employee_id'     => 1,
                        'releasing_stock' => true,
                        'receiving_stock' => false,
                    ],
                ],
            ],
            'enable_local_profile' => false,
            'delivery_locations'   => [
                [
                    'state_id'      => \App\Models\State::factory()->create()->id,
                    'selected_city' => true,
                    'cities'        => \App\Models\City::factory(3)->create()->select(['id', 'name'])->toArray(),
                ],
                [
                    'state_id'      => \App\Models\State::factory()->create()->id,
                    'selected_city' => false,
                ],
            ],
        ];

        $response = $this->put('/account/branches/'.$branch->id, $data);

        $response->assertStatus(302)
            ->assertSessionDoesntHaveErrors();

        $branch->refresh();

        expect($data['name'])->toBe($branch->name)
            ->and($data['description'])->toBe($branch->description)
            ->and($data['cr'])->toBe($branch->cr)
            ->and($data['tax_number'])->toBe($branch->tax_number)
            ->and((array) $data['phone'])->toBe((array) $branch->phone)
            ->and((array) $data['address'])->toMatchArray($branch->address)
            ->and($data['config'])->toBeArray()
            ->and($data['config']['number_of_shifts'])->toBe($branch->config['number_of_shifts'])
            ->and($data['config']['shift_working_hours'])->toMatchArray($branch->config['shift_working_hours'])
            ->and($data['config']['enable_global_profile'])->toBe($branch->config['enable_global_profile'])
            ->and($data['config']['enable_local_profile'])->toBe($branch->config['enable_local_profile']);

        foreach ($branch->deliveryLocations as $key => $location) {

            $raw_data = $data['config']['delivery_locations'][$key];

            expect($raw_data['state_id'])->toBe($location->state_id)
                ->and($branch->id)->toBe($location->branch_id)
                ->and($raw_data['selected_city'])->toBe($location->selected_city === 1);

            if ($raw_data['selected_city']) {
                expect($raw_data['cities'])->toBe($location->cities);
            }

        }

        if (isset($data['addresses'])) {

            expect($branch->addresses()->count())->toBe(2)
                ->and(\App\Models\AddressAssignEmployee::count())->toBe(2);

            foreach ($data['addresses'] as $key => $address) {
                $address_model = $branch->addresses[$key];
                $employee      = $address_model->employee;

                expect($address['address_name'])->toBe($address_model->address_name)
                    ->and($address['address_operations'])->toBe($address_model->address_operations)
                    ->and($address['address_purpose'])->toBe($address_model->address_purpose)
                    ->and((array) $address['address'])->toMatchArray($address_model->address)
                    ->and($address['employee']['employee_id'])->toBe($employee->employee_id)
                    ->and($address['employee']['releasing_stock'])->toBe((bool) $employee->releasing_stock)
                    ->and($address['employee']['receiving_stock'])->toBe((bool) $employee->receiving_stock);

            }
        }

        Storage::disk('public')->assertExists(
            $branch->getMedia('covers')->first()->getPathRelativeToRoot()
        );

        Storage::disk('public')->assertExists(
            $branch->getMedia('crs')->first()->getPathRelativeToRoot()
        );

    });

    it('can validate rules', function ($field) {

        [$account , $branch , $user] = prapperData();
        [$data , $fields]            = populateFields($field);

        createPermissions($user, ['create branch']);

        $this->actingAs($user);

        $data['cr_image']    = populateFiles();
        $data['cover_image'] = populateFiles();
        $data['image']       = populateFiles();
        $data['account_id']  = $account->id;

        $response = $this->put('/account/branches/'.$branch->id, $data);

        expect(\App\Models\Branch::count())->toBe(2)
            ->and($response->status())->toBe(302)
            ->and($response->assertSessionHasErrors($fields));

    })->with([
        ['field' => 'name'],
        ['field' => 'cr'],
        ['field' => 'phone'],

    ])->assignee('xmohamedamin');

    it('can validate permission', function ($data) {

        [$account , $branch , $user] = prapperData();

        $this->actingAs($user);

        $response = $this->put('/account/branches/'.$branch->id, [...$data, 'account_id' => $account->id]);

        expect(\App\Models\Branch::count())->toBe(2)
            ->and($response->status())->toBe(403);

    })->with([
        [[
            'name'        => 'Create Name',
            'description' => 'update Name',
            'cr'          => 'update Name',
            'tax_number'  => '12344',
            'phone'       => ['code' => '232', 'number' => '2322222'],
            'address'     => [
                'country' => '1234',
                'state'   => '1234',
                'city'    => '1234',
                'street'  => '1234',
            ],
            'config' => [
                'working_days' => [
                    'sunday',
                    'monday',
                    'tuesday',
                ],
                'number_of_shifts'    => 2,
                'shift_working_hours' => [
                    [
                        'end' => '17:00', 'start' => '08:00',
                    ],
                ],
                'enable_global_profile' => true,
                'enable_local_profile'  => false,
            ],
        ],
        ],
    ])->assignee('xmohamedamin');

})->assignee('xmohamedamin');
