<?php

namespace Database\Factories;

use App\Models\EmployeeVisit;
use App\Models\EmployeeVisitLine;
use App\Models\OrderLine;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class EmployeeVisitLineFactory extends Factory
{
    protected $model = EmployeeVisitLine::class;

    public function definition(): array
    {
        return [
            'quantity'   => $this->faker->randomFloat(), //
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'order_line_id'     => OrderLine::factory(),
            'employee_visit_id' => EmployeeVisit::factory(),
        ];
    }
}
