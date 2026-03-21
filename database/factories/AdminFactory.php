<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'position' => $this->faker->word(),
            'status'   => $this->faker->randomElement(['active', 'disabled']),
        ];
    }
}
