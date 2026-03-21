<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'total'        => $this->faker->randomFloat(),
            'paid_at'      => Carbon::now(),
            'delivered_at' => Carbon::now(),
            'status'       => $this->faker->word(),
            'created_at'   => Carbon::now(),
            'updated_at'   => Carbon::now(),

            'branch_id'   => Branch::factory(),
            'employee_id' => Employee::factory(),
        ];
    }
}
