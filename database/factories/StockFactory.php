<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Variant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StockFactory extends Factory
{
    protected $model = Stock::class;

    public function definition(): array
    {
        return [
            'price'      => $this->faker->randomFloat(),
            'quantity'   => $this->faker->randomNumber(),
            'tiers'      => [],
            'packaging'  => $this->faker->word(),
            'image'      => $this->faker->word(),
            'images'     => $this->faker->words(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'product_id' => Product::factory(),
            'variant_id' => Variant::factory(),
            'branch_id'  => Branch::factory(),
        ];
    }
}
