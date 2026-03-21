<?php

namespace Database\Seeders;

use App\Models\ScheduleVisit;
use Illuminate\Database\Seeder;

class ScheduleVisitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ScheduleVisit::factory()->count(10)->create();
    }
}
