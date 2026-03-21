<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\SavedList;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class SavedListFactory extends Factory
{
    protected $model = SavedList::class;

    public function definition(): array
    {
        return [
            'name'       => $this->faker->name(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'branch_id'   => Branch::factory(),
            'employee_id' => Employee::factory(),
        ];
    }
}
