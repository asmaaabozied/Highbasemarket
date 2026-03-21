<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\QuoteProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteProductFactory extends Factory
{
    protected $model = QuoteProduct::class;

    public function definition(): array
    {
        return [
            'product' => json_encode([
                'name'  => $this->faker->word,
                'image' => $this->faker->imageUrl(),
            ]),
            'quotable_id'         => Product::factory(),
            'quotable_type'       => Product::class,
            'price'               => $this->faker->randomFloat(2, 5, 500),
            'temperature'         => $this->faker->randomFloat(1, 0, 100),
            'total_price'         => $this->faker->randomFloat(2, 10, 2000),
            'quantity'            => $this->faker->numberBetween(1, 10),
            'size'                => $this->faker->numberBetween(10, 100),
            'pack'                => $this->faker->randomElement(['P', 'B', 'C']),
            'unit'                => $this->faker->randomElement(['Unit', 'Box', 'Pack']),
            'tech_specifications' => $this->faker->sentence,
        ];
    }
}
