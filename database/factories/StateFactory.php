<?php

namespace Database\Factories;

use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\State>
 */
class StateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'         => $this->faker->name,
            'state_code'   => 'BDS',
            'country_id'   => 1,
            'country_code' => 'AF',
            'country_name' => $this->faker->name,
            'latitude'     => '36.68333000',
            'longitude'    => '71.53333000',
        ];
    }
}
