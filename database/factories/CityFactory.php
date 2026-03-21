<?php

namespace Database\Factories;

use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\City>
 */
class CityFactory extends Factory
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
            'state_id'     => State::factory(),
            'state_code'   => 'BDS',
            'state_name'   => $this->faker->name,
            'country_id'   => 1,
            'country_code' => 'AF',
            'country_name' => $this->faker->name,
            'latitude'     => '36.68333000',
            'longitude'    => '71.53333000',
            'wikiDataId'   => 'Q4805192',
        ];
    }
}
