<?php

namespace Database\Factories;

use App\Models\CustomerVendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CustomerVendorsFactory extends Factory
{
    protected $model = CustomerVendor::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'config'     => $this->faker->words(),
        ];
    }
}
