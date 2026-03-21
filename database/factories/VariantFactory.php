<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Variant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VariantFactory extends Factory
{
    protected $model = Variant::class;

    public function definition(): array
    {
        return [
            'name'       => $this->faker->name(),
            'country'    => $this->faker->country(),
            'barcode'    => $this->faker->word(),
            'main'       => $this->faker->boolean(),
            'image'      => $this->faker->word(),
            'images'     => $this->faker->words(),
            'status'     => $this->faker->word(),
            'attributes' => [
                'color' => $this->faker->word(),
                'size'  => $this->faker->word(),
            ],
            'packages' => [
                [
                    'name'     => $this->faker->word(),
                    'quantity' => $this->faker->numberBetween(1, 10),
                    'unit'     => $this->faker->word(),
                ],
            ],
            'description' => $this->faker->text(),
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
            'product_id'  => Product::factory(),
        ];
    }
}
