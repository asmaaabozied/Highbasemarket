<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visit>
 */
class VisitFactory extends Factory
{
    protected $model = Visit::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'branch_id'   => Branch::factory(),
            'vendor_id'   => Branch::factory(),
            'visited_at'  => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
