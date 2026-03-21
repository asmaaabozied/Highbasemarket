<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            [
                'name'      => 'Branches',
                'type'      => 'services',
                'attribute' => [
                    ['name' => 'count', 'type' => 'text', 'value' => 50],
                    ['name' => 'depth', 'type' => 'text', 'value' => 5],
                ],
                'status' => 1,
            ],
            [
                'name'      => 'Employees',
                'type'      => 'services',
                'attribute' => [
                    ['name' => 'count', 'type' => 'text', 'value' => '20'],
                    ['name' => 'rulesCount', 'type' => 'text', 'value' => '20'],
                    ['name' => 'customRule', 'type' => 'select', 'value' => 'true', 'options' => [
                        ['option' => true],
                        ['option' => false],
                    ]],
                ],
                'status' => 1,
            ],
            [
                'name'      => 'Market',
                'type'      => 'services',
                'attribute' => [
                    ['name' => 'globalMarket', 'type' => 'select', 'value' => true, 'options' => [
                        ['option' => true],
                        ['option' => false],
                    ]],
                    ['name' => 'localMarket', 'type' => 'select', 'value' => true, 'options' => [
                        ['option' => true],
                        ['option' => false],
                    ]],
                ],
                'status' => 1,
            ],
            [
                'name'      => 'Customers',
                'type'      => 'services',
                'attribute' => [
                    ['name' => 'allow', 'type' => 'select', 'value' => true, 'options' => [
                        ['option' => true],
                        ['option' => false],
                    ]],
                ],
                'status' => 1,
            ],
            [
                'name'      => 'Providers',
                'type'      => 'services',
                'attribute' => [
                    ['name' => 'allow', 'type' => 'select', 'value' => false, 'options' => [
                        ['option' => true],
                        ['option' => false],
                    ]],
                ],
                'status' => 1,
            ],
            [
                'name'      => 'Support',
                'type'      => 'services',
                'attribute' => [
                    ['name' => 'type', 'type' => 'select', 'value' => 'default', 'options' => [
                        ['option' => 'default'],
                        ['option' => 'other'],
                    ]],
                ],
                'status' => 1,
            ],
            [
                'name'      => 'Add Customer',
                'type'      => 'global',
                'attribute' => [
                    [
                        'name'  => 'numberOfRequests',
                        'type'  => 'text',
                        'value' => 0,
                    ],
                    [
                        'name'  => 'amountPerRequest',
                        'type'  => 'text',
                        'value' => 0,
                    ],
                    [
                        'name'  => 'is_percentage',
                        'type'  => 'checkbox',
                        'value' => false,
                    ],
                ],
                'status' => 1,
            ],
            [
                'name'      => 'Order',
                'type'      => 'local',
                'attribute' => [
                    [
                        'name'  => 'first_commission_amount',
                        'type'  => 'text',
                        'value' => 0,
                    ],
                    [
                        'name'  => 'first_is_percentage',
                        'type'  => 'checkbox',
                        'value' => false,
                    ],

                    [
                        'name'  => 'others_commission_amount',
                        'type'  => 'text',
                        'value' => 0,
                    ],
                    [
                        'name'  => 'others_is_percentage',
                        'type'  => 'checkbox',
                        'value' => false,
                    ],
                    [
                        'name'  => 'add_to_customer_list',
                        'type'  => 'checkbox',
                        'value' => false,
                    ],
                ],
                'status' => 1,
            ],
        ];

        $cols = collect($modules)->whereNotIn('name', Module::query()->pluck('name')?->toArray());

        foreach ($cols as $col) {
            Module::query()->create($col);
        }
    }
}
