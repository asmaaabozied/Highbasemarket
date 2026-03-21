<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\CustomerSpecialPrice;
use App\Models\Employee;
use App\Models\SpecialPriceTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CustomerSpecialPriceFactory extends Factory
{
    protected $model = CustomerSpecialPrice::class;

    public function definition(): array
    {
        return [
            'notes'                     => $this->faker->word(),
            'created_at'                => Carbon::now(),
            'updated_at'                => Carbon::now(),
            'branch_id'                 => Branch::factory(),
            'customer_id'               => Branch::factory(),
            'special_price_template_id' => SpecialPriceTemplate::factory(),
            'employee_id'               => Employee::factory(),
        ];
    }
}
