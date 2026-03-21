<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'amount'            => $this->faker->randomFloat(),
            'status'            => $this->faker->word(),
            'confirmation_date' => Carbon::now(),
            'attachment'        => $this->faker->word(),
            'type'              => $this->faker->word(),
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),

            'order_id'     => Order::factory(),
            'branch_id'    => Branch::factory(),
            'employee_id'  => Employee::factory(),
            'confirmed_by' => Employee::factory(),
        ];
    }
}
