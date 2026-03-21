<?php

namespace Database\Factories;

use App\Models\OrderLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderLineFactory extends Factory
{
    protected $model = OrderLine::class;

    public function definition(): array
    {
        $price    = $this->faker->randomFloat(2, 10, 500);
        $quantity = $this->faker->numberBetween(1, 10);

        return [
            'price'    => $price,
            'quantity' => $quantity,
            'total'    => $price * $quantity,
            'status'   => 'pending',
        ];
    }
}
