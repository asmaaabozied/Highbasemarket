<?php

namespace Database\Factories;

use App\Models\SpecialPrice;
use App\Models\SpecialPriceTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class SpecialPriceFactory extends Factory
{
    protected $model = SpecialPrice::class;

    public function definition(): array
    {
        return [
            'amount'       => $this->faker->randomFloat(),
            'type'         => $this->faker->word(),
            'is_increment' => $this->faker->boolean(),
            'created_at'   => Carbon::now(),
            'updated_at'   => Carbon::now(),

            'special_price_template_id' => SpecialPriceTemplate::factory(),
        ];
    }
}
