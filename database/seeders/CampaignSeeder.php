<?php

namespace Database\Seeders;

use App\Models\Campaign;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        Campaign::create([
            'name'  => 'Gulfood 2025 (1)',
            'Links' => [
                [
                    'platform' => 'LinkedIn',
                    'ref'      => 'gulfood2025linkedin1',
                ],
                [
                    'platform' => 'Email',
                    'ref'      => 'gulfood2025email1',
                ],
            ],
            'status' => 'active',
        ]);

        Campaign::create([
            'name'  => 'Gulfood 2025 (2)',
            'Links' => [
                [
                    'platform' => 'LinkedIn',
                    'ref'      => 'gulfood2025linkedin2',
                ],
                [
                    'platform' => 'Email',
                    'ref'      => 'gulfood2025email2',
                ],
            ],
            'status' => 'active',
        ]);

        Campaign::create([
            'name'  => 'Gulfood 2025 (3)',
            'Links' => [
                [
                    'platform' => 'LinkedIn',
                    'ref'      => 'gulfood2025linkedin3',
                ],
                [
                    'platform' => 'Email',
                    'ref'      => 'gulfood2025email3',
                ],
            ],
            'status' => 'active',
        ]);
    }
}
