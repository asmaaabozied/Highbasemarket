<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            ['name' => 'admin', 'except' => [], 'extra' => [], 'for' => 'admins'],
            ['name' => 'role', 'except' => [], 'extra' => [], 'for' => ''],
            ['name' => 'brand', 'except' => [], 'extra' => [], 'for' => 'admins'],
            ['name' => 'account', 'except' => ['create'], 'extra' => [], 'for' => 'admins'],
            ['name' => 'member', 'except' => [], 'extra' => [], 'for' => 'accounts'],
            ['name' => 'branch', 'except' => [], 'extra' => [], 'for' => ''],
            ['name' => 'product', 'except' => [], 'extra' => [], 'for' => ''],
            ['name' => 'product group', 'except' => [], 'extra' => [], 'for' => 'admins'],
            ['name' => 'category', 'except' => [], 'extra' => [], 'for' => 'admins'],
            [
                'name'   => 'order',
                'except' => [],
                'extra'  => ['pay purchase', 'approve order', 'ship order', 'reject order', 'deliver order'],
                'for'    => '',
            ],
            ['name' => 'quote', 'except' => [], 'extra' => [], 'for' => ''],
            ['name' => 'stock', 'except' => [], 'extra' => [], 'for' => ''],
            ['name' => 'product list', 'except' => [], 'extra' => [], 'for' => 'accounts'],
            ['name' => 'news', 'except' => [], 'extra' => [], 'for' => ''],
            ['name' => 'interest', 'except' => [], 'extra' => [], 'for' => 'accounts'],
            [
                'name'  => 'purchase', 'except' => ['delete'],
                'extra' => [],
                'for'   => '',
            ],
            ['name' => 'payment', 'except' => [], 'extra' => [], 'for' => ''],
            ['name' => 'setting', 'except' => [], 'extra' => [], 'for' => ''],
            ['name' => 'subscription', 'except' => [], 'extra' => [], 'for' => 'accounts'],
            ['name' => 'shipment', 'except' => [], 'extra' => [], 'for' => 'accounts'],
            ['name' => 'supply term', 'except' => [], 'extra' => [], 'for' => 'accounts'],
            ['name' => 'progress', 'except' => [], 'extra' => [], 'for' => 'accounts'],
            ['name' => 'RFQ', 'except' => [], 'extra' => [], 'for' => 'accounts'],
            ['name' => 'customer', 'except' => ['create', 'update'], 'extra' => [], 'for' => 'accounts'],
            ['name' => 'vendor', 'except' => ['create', 'update'], 'extra' => [], 'for' => 'accounts'],
            ['name' => 'message', 'except' => ['update', 'create'], 'extra' => [], 'for' => ''],
            ['name' => 'order payment', 'except' => [], 'extra' => [], 'for' => 'accounts'],
            ['name' => 'bill payment', 'except' => [], 'extra' => [], 'for' => ''],
            ['name' => 'special price template', 'except' => [], 'extra' => [], 'for' => ''],
            [
                'name'   => 'customer special price',
                'except' => ['create', 'update', 'delete', 'view', 'view all'],
                'extra'  => ['assign special price template', 'remove special price template'],
                'for'    => '',
            ],
            ['name' => 'schedule visit', 'except' => [], 'extra' => [], 'for' => ''],
            [
                'name'   => 'employee visit',
                'except' => [],
                'extra'  => [
                    'view timeline',
                    'remove future visit',
                    'postponed future visit',
                    'reschedule future visit',
                ], 'for' => '',
            ],

            [
                'name'   => 'instant order',
                'except' => ['delete', 'view', 'view all', 'update'],
                'extra'  => [
                    'approve instant order',
                    'ship instant order',
                    'deliver instant order',
                ],
                'for' => '',
            ],

        ];

        $permissions = ['create', 'update', 'delete', 'view', 'view all'];

        $data = [];

        foreach ($modules as $module) {
            foreach ($permissions as $permission) {
                $module_name = $module['name'];

                if ($permission === 'view all') {
                    $module['name'] = Str::plural($module['name'], 3);
                }

                if (Permission::where('name', "$permission {$module['name']}")->exists()) {
                    continue;
                }

                if (! in_array($permission, $module['except'])) {
                    $data[] = [
                        'name' => "$permission {$module['name']}", 'module' => $module_name, 'for' => $module['for'],
                    ];
                }
            }

            foreach ($module['extra'] as $permission) {
                if (Permission::where('name', $permission)->exists()) {
                    continue;
                }

                $data[] = ['name' => "$permission", 'module' => $module_name, 'for' => $module['for']];
            }
        }

        $this->removeOldPermissions();
        $this->removeModulePermissions();
        $this->changePermissionsModules();

        Permission::insert($data);
    }

    private function removeOldPermissions(): void
    {
        $permission = [
            'reject instant order',
            'update instant order',
            'view instant order',
            'view all instant orders',
            'pay instant order',
        ];

        Permission::whereIn('name', $permission)->delete();
    }

    private function changePermissionsModules(): void
    {
        Permission::where('name', 'create instant order')
            ->update(['module' => 'instant order']);
    }

    private function removeModulePermissions(): void
    {
        Permission::where('module', 'visit')->delete();
    }
}
