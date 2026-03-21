<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'internal_id' => $this->faker->randomNumber(4),
            'sequence'    => $this->faker->numberBetween(1, 100),
            'slug'        => $this->faker->slug,
            'name'        => $this->faker->name,
            'image'       => $this->faker->image,
            'description' => $this->faker->text,
            'full_name'   => $this->faker->word,
            'barcode'     => $this->faker->randomNumber(8),
        ];
    }
}
