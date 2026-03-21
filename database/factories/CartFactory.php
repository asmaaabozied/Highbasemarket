<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Cart;
use App\Models\Employee;
use App\Models\Stock;
use App\Models\Variant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            'quantity'   => $this->faker->randomNumber(),
            'packaging'  => $this->faker->words(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'branch_id'         => Branch::factory(),
            'branch_product_id' => Stock::factory(),
            'variant_id'        => Variant::factory(),
            'employee_id'       => Employee::factory(),
        ];
    }
}
