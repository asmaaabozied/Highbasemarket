<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\SpecialPriceTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class SpecialPriceTemplateFactory extends Factory
{
    protected $model = SpecialPriceTemplate::class;

    public function definition(): array
    {
        return [
            'name'        => $this->faker->name(),
            'description' => $this->faker->text(),
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),

            'branch_id' => Branch::factory(),
        ];
    }
}
